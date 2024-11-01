(function($){

	var coachingJson = {};

	$(function(){
		$('body')
			.on('click', '.rncbc-timeslots .slot', function(){
				if($(this).hasClass('disable'))
					return false;

				$(this).toggleClass('on');
			})
			.on('click', '.remove-ts', function(){
				var day = $(this).parent().attr('data-day');
				var parent = $(this).parent().parent();
				var id = parent.attr('data-id');
				$(this).parent().remove();

				var slots = [];
				parent.find('.item[data-day="'+ day +'"]').each(function(){
					slots.push($(this).attr('data-index'));
				});

				if(slots.length) {
					window.rncbcCalendar['calendar_'+ id].booked[day] = slots.join(',');
				} else {
					window.rncbcCalendar['calendar_'+ id].calendar.deselectDates(new Date(day));
					delete window.rncbcCalendar['calendar_'+ id].booked[day];
				}
			});

		$('.rncbc-people').change(function(){
			var id = $(this).attr('data-id');
			var p = parseInt($(this).val());
			$('#rncbc-calendar-timeslots-'+id).find('.item').each(function(){
				var cap = parseInt($(this).attr('data-cap'));

				if(cap < p) {
					$(this).find('a').trigger('click');
				}
			});

		});

		$('.rncbc_submit').click(function() {
			var btn = $(this);
			var txt = btn.val();
			//window.rncbcCalendarAjaxUrl
			var id = $(this).attr('data-id');
			var form = $('#rncbc_calendar_form_'+id);

			if(!form.find('input[name="day"]').val()) {
				alert('Please pick an available day.');
				return false;
			}

			if(!form.find('input[name="court_id"]').val()) {
				alert('Please select an available court.');
				return false;
			}

			if(!form.find('input[name="name"]').val()) {
				alert('Please input your name.');
				return false;
			}

			if(!form.find('input[name="email"]').val()) {
				alert('Please input your email.');
				return false;
			}

			if(!form.find('input[name="phone"]').val()) {
				alert('Please input your phone number.');
				return false;
			}

			btn.val('loading...');
			$.ajax({
				url: window.rncbcCalendarAjaxUrl + '?action=rncbc_ajax_booking',
				type: 'post',
				dataType: 'json',
				data: form.serialize(),
				success: function(data) {
					btn.val(txt);
					alert(data.msg);
					if(!data.error) {
						if(data.payment_url) {
							window.location.href = data.payment_url;
						} else {
							window.location.reload();
						}

					}
				}
			})

		});

		$('.rncbc_coaching_submit').click(function() {
			var btn = $(this);
			var txt = btn.val();
			var id = $(this).attr('data-id');
			var form = $('#rncbc_coaching_form_'+id);

			if(!form.find('input[name="day"]').val()) {
				alert('Please pick an available day.');
				return false;
			}

			if(!form.find('input[name="name"]').val()) {
				alert('Please input your name.');
				return false;
			}

			if(!form.find('input[name="email"]').val()) {
				alert('Please input your email.');
				return false;
			}

			if(!form.find('input[name="phone"]').val()) {
				alert('Please input your phone number.');
				return false;
			}

			btn.val('loading...');
			$.ajax({
				url: window.rncbcCalendarAjaxUrl + '?action=rncbc_ajax_coaching',
				type: 'post',
				dataType: 'json',
				data: form.serialize(),
				success: function(data) {
					btn.val(txt);
					alert(data.msg);
					if(!data.error) {
						if(data.payment_url) {
							window.location.href = data.payment_url;
						} else {
							window.location.reload();
						}

					}
				}
			})

		});

		YUI().use('calendar', 'datatype-date', 'datatype-date-math', 'panel', function(Y) {


//			Y.CalendarBase.CONTENT_TEMPLATE = Y.CalendarBase.TWO_PANE_TEMPLATE;
			$.each(window.rncbcCalendar, function(k, v){
				var config = {
					contentBox: "#rncbc-calendar-"+ v.id,
					showPrevMonth: true,
					showNextMonth: true,
//					selectionMode: 'multiple-sticky',
					enabledDatesRule: 'enabled_date',
					minimumDate: Y.Date.parse(v.min_day),
					date: new Date()
				};

				if(v.max_day)
					config.maximumDate = Y.Date.parse(v.max_day);

				var enabledDatesRule = {
					"all": {
						"all": {
							"all": {}
						}
					}
				};
				enabledDatesRule.all.all.all[window.rncbcCalendar[k].working_day] = "enabled_date";
				if(window.rncbcCalendar[k].activity) {
					enabledDatesRule.all.all.all[window.rncbcCalendar[k].activity] = "activity_date";
				}


				var calendarObject;
				calendarObject = new Y.Calendar(config).render();
				calendarObject.set("customRenderer", {
					rules: enabledDatesRule,
					filterFunction: function (date, node, rules) {
						if (Y.Array.indexOf(rules, 'enabled_date') >= 0) {
							node.addClass('enabled_date');
						}

						if (Y.Array.indexOf(rules, 'activity_date') >= 0) {
							node.addClass('activity_date');
						}
					}
				});




				calendarObject.on('dateClick', function(ev){
					//calendarObject.deselectDates(ev.date);
					//var people = $('#rncbc_calendar_form_'+ v.id).find('select').val();



				});



				calendarObject.on("selectionChange", function (ev) {
					if(v.loading) {
						ev.stopPropagation();
						return false;
					}

					var day = Y.Date.format(new Date(ev.newSelection[0]), {format:"%F"});
					var wrap = $('#rncbc_calendar_table_'+ v.id);
					$('#rncbc_calendar_form_'+ v.id).hide();
					wrap.find('.loading').html('Loading...').show();
					wrap.find('table').remove();
					$.ajax({
						url: window.rncbcCalendarAjaxUrl + '?action=rncbc_day_data',
						data:{day:day, calendar_id:v.id},
						dataType: "json",
						method: "get",
						success: function(data) {
							if(data.error) {
								alert(data.msg);
							} else {
								wrap.find('.loading').hide();
								wrap.append(data.html);
								coachingJson = data.coaching_json;
							}
						},
						complete: function() {
							v.loading = false;
						}
					})
				});

				v.calendar = calendarObject;

			});

			$('body').on('click', '.time_slot', function(){
				if($(this).hasClass('u') || $(this).hasClass('last'))
					return false;

				if($(this).hasClass('coaching')) {
					coachingForm($(this));
					return false;
				}

				var table = $(this).parent().parent().parent();
				var form = $('#rncbc_calendar_form_'+table.attr('data-id'));
				var court_id = $(this).attr('data-court-id');
				form.find('input[name="court_id"]').val(court_id);
				form.find('input[name="day"]').val(table.attr('data-day'));
				form.find('.court-name span').html($('#court_'+court_id).html());
				var fromTime = $(this).attr('data-time');
				form.find('.court-from span').html(fromTime);
				form.find('.court-from input').val(fromTime);


				var toHtml = '';
				var fromTimeInt = parseInt(fromTime.replace(":", ""));
				var start = 0;
				var last = false;
				var tmp = false;
				var i = 1;
				var hasCoaching = false;
				$('.time_'+court_id).each(function(){
					tmp = parseInt($(this).attr('data-time').replace(":", ""));

					if($(this).hasClass('coaching') && tmp > fromTimeInt) {
						last = $(this).attr('data-time');
						hasCoaching = true;
						return false;
					}

					if($(this).hasClass('u') && tmp > fromTimeInt)
						return false;


					if(tmp <= fromTimeInt)
						return true;

					if($(this).hasClass('last')) {
						last = $(this).attr('data-time');
						return false;
					}


                    start += 0.5;
					toHtml += '<option value="'+ $(this).attr('data-time') + '" data-eq='+ i +'>'+ $(this).attr('data-time') + ' [' + (start) +' hour(s)]' +'</option>';
					//
					last = $(this).attr('data-time');

					i++;
				});

				if(toHtml && last) {
					//table
					var tmpLast = last.split(':');
					var lastInt = parseInt(last.replace(':', ''));

					if(hasCoaching) {
						var toInt = parseInt(last.replace(':', ''));
					} else {
						var toInt = parseInt(table.attr('data-to'));
					}

					if( lastInt < toInt) {
						if(tmpLast[1] == 30) {
							last = parseInt(tmpLast[0]) + 1;
							last = last + '';
							if(last.length == 1) last = '0' + last;
							last = last +':00';
						} else {
							last = tmpLast[0] +':30';
						}
						toHtml += '<option value="'+ last + '" data-eq='+ i +'>'+ last + ' [' + (start+0.5) +' hour(s)]' +'</option>';
					} else if(lastInt == toInt) {
						toHtml += '<option value="'+ last + '" data-eq='+ i +'>'+ last + ' [' + (start+0.5) +' hour(s)]' +'</option>';
					}
				}

				if(!toHtml && tmp > fromTimeInt) {
					var time = fromTime.split(':');
					if(time[1] == 30) {
						time = (parseInt(time[0]) + 1) + ':00';
					} else {
						time = time[0] + ':30';
					}
					//single time slot
					toHtml += '<option value="'+ time + '" data-eq='+ i +'>'+ time + ' [0.5 hour(s)]' +'</option>';
				}

				var select = form.find('select[name="to"]');
				select.html(toHtml);
				$('.rncbc_form').hide();
				form.show();

				var price = parseFloat($('#court_'+court_id).attr('data-price'));

				var priceObj = form.find('.total');
				if(priceObj) {
					priceObj.html(price);
					select.change(function(){
                        var isMemberSelect = form.find('select[name="is_member"]');
                        var useMemberPrice = isMemberSelect && isMemberSelect.val() == '1';
                        if(useMemberPrice) {
                            price = parseFloat($('#court_'+court_id).attr('data-member-price'));
                        } else {
                            price = parseFloat($('#court_'+court_id).attr('data-price'));
						}
						priceObj.html( totalMoney(price, parseInt($(this).find('option:selected').attr('data-eq'))) );
					});
				}
				if(form.find('select[name="is_member"]')) {
                    form.find('select[name="is_member"]').change(function () {
                        select.trigger('change');
                    })
				}


				$('body').scrollTo('#rncbc_calendar_form_'+$(this).parent().parent().parent().attr('data-id'), {duration:'slow', offsetTop : '50'});

			})
		});

	});

	var coachingForm = function(obj) {
		if(obj.hasClass('full')) {
			return false;
		}

		var coachingId = obj.attr('data-coaching');
		var calendarId = obj.parent().parent().parent().attr('data-id');
		var coaching = coachingJson['coaching_'+coachingId];
		var form = $('#rncbc_coaching_form_'+calendarId);
		form.find('input[name="id"]').val(coachingId);
		var table = obj.parent().parent().parent();
		form.find('input[name="day"]').val(table.attr('data-day'));

		form.find('.coaching-title span').html(coaching.title);
		form.find('.coaching-name span').html(coaching.coach_name);
		form.find('.coaching-detail div').html(coaching.description);

		var select = '';
		var left = coaching.capacity - coaching.people;
		if(left <= 0) {
			alert('Sorry, this event is fully booked out.');
			return false;
		} else {
			for(var i=1;i<=left;i++) {
				select += '<option value="' + i + '">' + i + '</option>'
			}
		}
		form.find('select[name="people"]').html(select);

		var price = coaching.price;
		var priceObj = form.find('.total');
		if(priceObj) {
			priceObj.html(price);
			form.find('select[name="people"]').change(function(){
				priceObj.html( totalMoney(price, parseInt($(this).find('option:selected').val())) )
			});
		}

		$('.rncbc_form').hide();
		form.show();

		$('body').scrollTo('#rncbc_coaching_form_'+calendarId, {duration:'slow', offsetTop : '50'});

	};

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

	$.mergeJsonObject = function(jsonbject1, jsonbject2) {
		var resultJsonObject={};
		for(var attr in jsonbject1){
			resultJsonObject[attr]=jsonbject1[attr];
		}
		for(var attr in jsonbject2){
			resultJsonObject[attr]=jsonbject2[attr];
		}

		return resultJsonObject;
	};

	var slotsHtml = function(day, hour, minutes, cap, displayDay) {


		var html = '<div class="item" data-day="'+ day +'" data-index="'+ (hour +':'+ minutes+':' + cap) +'" data-cap="'+ cap +'">';
		html += '<input type="hidden" name="slots['+ day +'][]" value="'+ (hour +':'+ minutes) +'" />';
		html += displayDay + ' ' +hour +':'+ minutes +'<a href="javascript:;" class="remove-ts">X</a>';
		html += '</div>';
		return html;
	};


	var totalMoney = function(price, qty) {
		price = parseInt(price * 100);
		return price * qty / 100;
	}

}(jQuery));