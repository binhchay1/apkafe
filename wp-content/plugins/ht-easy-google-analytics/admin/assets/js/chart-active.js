;( function ( $ ) {
    'use strict';

    $(document).ready(function(){
		const $select_account 	= $('.htga4-select-account'),
			$select_property 	= $('.htga4-select-property'),
			$select_measurement = $('.htga4-select-measurement-id');

		// Listen to select account
		$select_account.on('change', function(e){
			var account = this.value;

			if( account ){
				// Request properties list
				$.ajax({
					url: htga4_params.ajax_url,
					type: 'POST',
					//dataType: 'json',
					data: {
						'action': 'htga4_get_properties',
						'nonce' : htga4_params.nonce,
						'account': account
					},
			
					beforeSend:function(){ 
						console.log('loading...');
						$select_property.html('<option value="">Loading . . .</option>');
					},
			
					success:function(response) {
						$select_property.html('<option value="">Select property</option>');
						// Prepare & append dropdown options
						for (let [key, value] of Object.entries(response.data)) {
							$select_property.append(`<option value="${key}">${value} <${key}></option>`);
						}

						$select_property.removeAttr('disabled', '');
					},
			
					complete:function( response ){
						console.log('remove loading...');
					},
			
					error: function(errorThrown){
						console.log(errorThrown);
					}
				});
				
			} else {
				$select_property.html('<option value="">Select account</option>');
				$select_measurement.html('<option value="">Select measurement ID</option>');
				$select_property.attr('disabled', 'disabled');
				$select_measurement.attr('disabled', 'disabled');
			}
		});

		// Listen to select property
		$select_property.on('change', function(e){
			var property = this.value;

			if( property ){
				// Request measurement id list
				$.ajax({
					url: htga4_params.ajax_url,
					type: 'POST',
					//dataType: 'json',
					data: {
						'action': 'htga4_get_data_streams',
						'nonce' : htga4_params.nonce,
						'property': property
					},
			
					beforeSend:function(){ 
						// console.log('loading...');
						$select_measurement.html('<option value="">Loading . . .</option>');
					},
			
					success:function(response) {
						$select_measurement.html('<option value="">Select measurement ID</option>');
						// Prepare & append dropdown options
						for (let [key, value] of Object.entries(response.data)) {
							$select_measurement.append(`<option value="${key}">${value} <${key}></option>`);
						}

						$select_measurement.removeAttr('disabled', '');
					},
			
					complete:function( response ){
						// console.log('remove loading...');
					},
			
					error: function(errorThrown){
						console.log(errorThrown);
					}
				});
				
			} else {
				$select_measurement.html('<option value="">Select measurement ID</option>');
				$select_measurement.attr('disabled', 'disabled');
			}
		});

		if ( typeof htga4_params.sessions != 'undefined' ) {
			// Get today's date
			let now = new Date();

			// Get the year, month, and day
			let year = now.getFullYear();
			let month = now.getMonth() + 1; // Note that January is 0
			let day = now.getDate();

			let max_date = year + '-' + month +'-'+ day;

			let args = {
				opens: 'left',
				"maxDate": max_date,
				locale: {
					format: 'Y-M-D'
				},
			}

			// Get start and end date from the URL and set it for auto select date range
			let params = new URLSearchParams(window.location.search);
			let date_range_str = params.get('date_range');

			if( date_range_str ){
				let start_date = date_range_str.split(',')[0];
				let end_date = date_range_str.split(',')[1];
	
				if( start_date && end_date ){
					args.startDate 	= start_date;
					args.endDate 	= end_date;
				}
			}

			$('.ht_easy_ga4_reports_filter_custom_range').daterangepicker(args);

			$('.ht_easy_ga4_reports_filter_custom_range').on('apply.daterangepicker', function(ev, picker) {
				window.location.href = window.location.href + '&date_range=' + picker.startDate.format('YYYY-MM-DD') + ',' + picker.endDate.format('YYYY-MM-DD');
			});

            // Bounce rate
			let bounce_rate = htga4_params.bounce_rate;

            if( typeof bounce_rate === 'object' && bounce_rate != null ){
                const bounceRateGrowth = calculateGrowth(htga4_params.bounce_rate.current_total,htga4_params.bounce_rate.previous_total);
                $('.ht_bounce_rate .ht_growth_count').text( bounceRateGrowth + '%' );

                if( bounceRateGrowth && bounceRateGrowth > 0 ){
                    $('.ht_bounce_rate div .dashicons').addClass('dashicons-arrow-up-alt');
                }else if( bounceRateGrowth && bounceRateGrowth < 0 ){
                    $('.ht_bounce_rate div .dashicons').addClass('dashicons-arrow-down-alt');
                }

				if( htga4_params.bounce_rate.current_dataset.length < 1 ){
					$('.ht_bounce_rate .ht_easy_ga4_report_card_head').html();
				}
            }
            
			// Overview counts
			$('.ht_session .ht_easy_ga4_report_card_head_count').text(htga4_params.sessions.current_total);
			$('.ht_pageview .ht_easy_ga4_report_card_head_count').text(htga4_params.page_views.current_total);
			$('.ht_bounce_rate .ht_easy_ga4_report_card_head_count').text(htga4_params.bounce_rate.current_total.toFixed(1) + '%');

			// Session growth
			const sessionGrowth = calculateGrowth(htga4_params.sessions.current_total,htga4_params.sessions.previous_total);
			$('.ht_session .ht_growth_count').text( sessionGrowth + '%' );

			if( sessionGrowth && sessionGrowth > 0 ){
				$('.ht_session div .dashicons').addClass('dashicons-arrow-up-alt');
			}else if( sessionGrowth && sessionGrowth < 0 ){
				$('.ht_session div .dashicons').addClass('dashicons-arrow-down-alt');
			}

			// Pageview growth
			const pageviewGrowth = calculateGrowth(htga4_params.page_views.current_total,htga4_params.page_views.previous_total);
			$('.ht_pageview .ht_growth_count').text( pageviewGrowth + '%' );

			if( pageviewGrowth && pageviewGrowth > 0 ){
				$('.ht_pageview div .dashicons').addClass('dashicons-arrow-up-alt');
			}else if( pageviewGrowth && pageviewGrowth < 0 ){
				$('.ht_pageview div .dashicons').addClass('dashicons-arrow-down-alt');
			}

		}
		
    }); // document ready


	function calculateGrowth(currentTotal,previousTotal) {
        if( currentTotal == 0 &&  previousTotal == 0){
            return 0;
        }

		previousTotal = previousTotal <= 1 ? 1 : previousTotal;
		console.log(currentTotal,previousTotal );
		const growth = ((currentTotal - previousTotal) / previousTotal) * 100;

		return Math.round(growth);
	}

	if ( typeof htga4_params.sessions === 'undefined' ) {
		return false;
	}


	/* Default Setting Variables */
	const current = {
		label: 'Current Period',
		color: '#00a19f'
	}
	const previous = {
		label: 'Previous Period',
		color: '#9ca3af',
		disable: true
	}
	const options = {
		responsive: true,
		elements: {
		line: {
			borderWidth: 3,
		},
		point: {
			pointStyle: 'circle',
			radius: 4,
			hoverRadius: 5,
		}
		},
		plugins: {
		legend: {
			position: 'bottom',
		},
		}
	}
	
	/* Sessions Chart */
	const ctx_sessions = document.getElementById('sessions-chart');
	var sessions_chart = '';
	if( ctx_sessions ){
		sessions_chart = new Chart(ctx_sessions, {
			type: 'line',
			data: {
			labels: htga4_params.sessions.labels,
			datasets: [
				{
				label: current.label,
				data: htga4_params.sessions.current_dataset,
				borderColor: current.color,
				pointBackgroundColor: current.color,
				},
				{
				label: previous.label,
				data: htga4_params.sessions.previous_dataset,
				borderColor: previous.color,
				pointBackgroundColor: previous.color,
				hidden: previous.disable
				}
			]
			},
			options: options
		});
	}

	/* Page View Chart */
	const ctx_page_view = document.getElementById('page-view-chart');
	var page_view_chart = '';
	if( ctx_page_view ){
		page_view_chart = new Chart(ctx_page_view, {
			type: 'line',
			data: {
			labels: htga4_params.page_views.labels,
			datasets: [
				{
				label: current.label,
				data: htga4_params.page_views.current_dataset,
				borderColor: current.color,
				pointBackgroundColor: current.color,
				},
				{
				label: previous.label,
				data: htga4_params.page_views.previous_dataset,
				borderColor: previous.color,
				pointBackgroundColor: previous.color,
				hidden: previous.disable
				}
			]
			},
			options: options
		});
	}


	/* Page View Chart 2 */
	const ctx_page_view2 = document.getElementById('page-view-chart2');
	var page_view_chart2 = '';
	if( ctx_page_view2 ){
		page_view_chart2 = new Chart(ctx_page_view2, {
			type: 'line',
			data: {
			labels: htga4_params.bounce_rate.labels,
			datasets: [
				{
				label: current.label,
				data: htga4_params.bounce_rate.current_dataset,
				borderColor: current.color,
				pointBackgroundColor: current.color,
				},
				{
				label: previous.label,
				data: htga4_params.bounce_rate.previous_dataset,
				borderColor: previous.color,
				pointBackgroundColor: previous.color,
				hidden: previous.disable
				}
			]
			},
			options: options
		});
	}

	/* Compare Function */
	const compare = document.getElementById('ht_easy_ga4_reports_compare_field');

	if(compare){
		compare.addEventListener('change', function() {
			if(this.checked) {
				sessions_chart.data.datasets[1].hidden = false
				page_view_chart.data.datasets[1].hidden = false
				page_view_chart2.data.datasets[1].hidden = false
			} else {
				sessions_chart.data.datasets[1].hidden = true
				page_view_chart.data.datasets[1].hidden = true
				page_view_chart2.data.datasets[1].hidden = true
			}
			
			sessions_chart.update()
			page_view_chart.update()
			page_view_chart2.update()
		})
	}

	const optionsPie = {
		responsive: true,
		plugins: {
			legend: {
				position: 'bottom',
			},
		}
	}

	/* User Types Chart */
	const ctx_user_types = document.getElementById('user-types-chart');
	if( ctx_user_types && htga4_params.user_types.labels.length ){
		const user_types_chart = new Chart(ctx_user_types, {
			type: 'pie',
			data: {
				labels: htga4_params.user_types.labels,
				datasets: [
					{
						label: 'User Types',
						data: htga4_params.user_types.values,
						backgroundColor: [
							'rgba(56, 189, 248, .6)',
							'rgba(34, 197, 94, .6)'
						],
						borderColor: [
							'rgba(56, 189, 248, .6)',
							'rgba(34, 197, 94, .6)'
						],
						borderWidth: 1
					}
				]
			},
			options: optionsPie
		});
	}


	const ctx_device_types = document.getElementById('device-types-chart');
	if( ctx_device_types && htga4_params.device_types.labels.length ){
		const device_types_chart = new Chart(ctx_device_types, {
			type: 'pie',
			data: {
				labels: htga4_params.device_types.labels,
				datasets: [
					{
						label: 'User Types',
						data: htga4_params.device_types.values,
						backgroundColor: [
							'rgba(56, 189, 248, .6)',
							'rgba(34, 197, 94, .6)',
							'rgba(241, 179, 130, 1)'
						],
						borderColor: [
							'rgba(56, 189, 248, .6)',
							'rgba(34, 197, 94, .6)'
						],
						borderWidth: 1
					}
				]
			},
			options: optionsPie
		});
	}

} )( jQuery );