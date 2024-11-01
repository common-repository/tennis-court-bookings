(function($){

	var holidayRules = {};
	var calendar1;

	var specialRules = {};
	var calendar2;

	var panel, currentSpecialDay;

	$(function(){
		var holidayIpt = $('input[name="holiday"]');

		$('body')
			.on('click', '.remove-ts', function(){
				if(confirm('Did you really want to remove this slot?')) {
					$(this).parent().remove();
				}
			});

		$('input[name="working_day[]"]').click(function(){
			var obj = $('#timeslots-'+$(this).attr('data-eq'));
			if($(this).prop('checked')) {
				obj.show();
			} else {
				obj.hide();
			}
		});


		$('#calendar_save').click(function(){
			if(!$('input[name="title"]').val())	{
				alert('Title is required.');
				return false;
			}

			var closed = $('input[name="window_close"]').val();
			if(closed) {
				if(isNaN(closed) || closed.indexOf('.') !== -1 || closed < 1) {
					alert('Invalid booking window closed time.');
					return false;
				}
			}

			var special_day_input = '';
			$.each(window.specialDefault, function(k, v){
				special_day_input += '<input type="hidden" name="special_day['+ k +']" value="'+ v.join(',') +'" />';
			});


			$('#calender_form').append(special_day_input).submit();
		});


		$('.add-time-slots').click(function(){
			var parent = $('#'+$(this).attr('data-parent'));
			var cap = parent.find('input[name="capacity"]').val();

			if(isNaN(cap) || cap.indexOf('.') !== -1 || cap < 0) {
				alert('Invalid capacity');
				return false;
			}

			var hour = parent.find('select[name="hour"]').val();
			var minutes = parent.find('select[name="minutes"]').val();

			var timeslots = parent.find('.rncbc-timeslots');

			var index = (hour +''+ minutes);
			if(timeslots.find('.item[data-index="'+ index +'"]').size()) {
				alert('Time slots exists!');
				return false;
			}

			var html = slotsHtml(hour, minutes, cap, $(this).attr('data-name'));

			var after = null;
			timeslots.find('.item').each(function(){
				var tmpIndex = parseInt($(this).attr('data-index'));
				if(tmpIndex < index) {
					after = $(this);
				}

				if(after && tmpIndex > index) {
					return false;
				}
			});

			if(after) {
				$(html).insertAfter(after);
			} else {
				parent.find('.rncbc-timeslots').prepend(html);
			}

			if(hour < 23) {
				parent.find('select[name="hour"] option[value="'+ (parseInt(hour)+1) +'"]').prop('selected', 'selected');
			}

		});



		if(typeof YUI != 'undefined') {
			YUI().use('calendar', 'datatype-date', 'datatype-date-math', 'panel', function(Y) {


				Y.CalendarBase.CONTENT_TEMPLATE = Y.CalendarBase.ONE_PANE_TEMPLATE;

				calendar1 = new Y.Calendar({
					contentBox: "#holiday-calendar",
					showPrevMonth: true,
					showNextMonth: true,
					selectionMode: 'multiple-sticky',
					date: new Date()
				}).render();

				holidayRules = dateArray2filter(window.holidayDefault, 'holiday');

				calendar1.set("customRenderer", {
					rules: holidayRules,
					filterFunction: function (date, node, rules) {
						if (Y.Array.indexOf(rules, 'holiday') >= 0) {
							calendar1.selectDates(date);
						}
					}
				});

				calendar1.on("selectionChange", function (ev) {
					var value = '';
					Y.Array.each(ev.newSelection, function(item){
						var tmp = Y.Date.format(new Date(item), {format:"%F"});
						value += value ? ','+tmp : tmp;
					});
					holidayIpt.val(value)
				});




			});
		}



		$('#court-create').click(function(){
			var parent = $(this).parent().parent();
			var name = parent.find('input[name="name"]').val();
			if(!name) {
				alert('Court name is required');
				return false;
			}
			var calendar_id = $(this).attr('data-calendar-id');
			var price = parent.find('input[name="price"]').val();
			var member_price = parent.find('input[name="member_price"]').val();
			$.ajax({
				url: ajaxurl + '?action=rncbc_ajax_court&method=create',
				type:'post',
				dataType:'json',
				data:{name:name, calendar_id:calendar_id, price:price,member_price:member_price},
				success: function(data) {
					alert(data.msg);

					if(!data.error) {
						window.location.reload();
					}
				}
			})
		});

		$('.court-update').click(function(){
			var parent = $(this).parent().parent();
			var name = parent.find('input[name="name"]').val();
			if(!name) {
				alert('Court name is required');
				return false;
			}
			var id = parent.attr('data-id');
			var price = parent.find('input[name="price"]').val();
			var member_price = parent.find('input[name="member_price"]').val();

			$.ajax({
				url: ajaxurl + '?action=rncbc_ajax_court&method=update',
				type:'post',
				dataType:'json',
				data:{name:name, id:id, price:price,member_price:member_price},
				success: function(data) {
					alert(data.msg);

					if(!data.error) {
						//window.location.reload();
					}
				}
			})
		});

		$('.court-delete').click(function(){
			if(!confirm('Are you sure?'))
				return false;

			var parent = $(this).parent().parent();
			var id = parent.attr('data-id');

			$.ajax({
				url: ajaxurl + '?action=rncbc_ajax_court&method=delete',
				type:'post',
				dataType:'json',
				data:{id:id},
				success: function(data) {


					if(!data.error) {
						parent.remove();
					} else {
						alert(data.msg);
					}
				}
			})
		});



		$(".nav-tab-switch").click(function(){
			$(".nav-tab-content").hide();
			$(".nav-tab-active").removeClass("nav-tab-active");
			$("#"+ $(this).attr("data-show")).show();
			$(this).addClass("nav-tab-active");
		});
	});


	var dateArray2filter = function (dates, name) {
		var ret = {};
		for (var i in dates) {
			var d = new Date(dates[i]),
				y = d.getFullYear(),
				m = d.getMonth();
			if (!ret[y]) ret[y] = {};
			if (!ret[y][m]) ret[y][m] = {};
			ret[y][m][d.getDate()] = name;
		}
		return ret;
	};

	var slotsHtml = function(hour, minutes, cap, name) {
		var html = '<div class="item" data-index="'+ (hour +''+ minutes) +'">';
		html += '<input type="hidden" name="' + name + '" value="'+ (hour +':'+ minutes +':'+ cap) +'" />';
		html += hour +':'+ minutes +'<a href="javascript:;" class="remove-ts">X</a>';
		html += '</div>';
		return html;
	}

}(jQuery));