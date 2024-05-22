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
		
    }); // document ready

	// Line Charts
	const lineCharts = document.querySelectorAll('.ht_easy_ga4_line_chart');
	const lineChartsInstances = [];
	
	lineCharts.forEach( function( lineChartCanvas, key ){
		if( !lineChartCanvas.dataset.report ){
			return;
		}

		const reportData = JSON.parse(lineChartCanvas.dataset.report);
		const labels = reportData.labels;
		const currentDataset = reportData.current_dataset;
		const previousDataset = reportData.previous_dataset;
		const args = {
			type: 'line',
			data: {
				labels: labels,
				datasets: [
					{
						label: 'Current Period',
						data: currentDataset,
						borderColor: '#00a19f',
						pointBackgroundColor: '#00a19f',
					},
					{
						label: 'Previous Period',
						data: previousDataset,
						borderColor: '#9ca3af',
						pointBackgroundColor: '#9ca3af',
						hidden: true
					}
				]
			},
			options: {
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
		};

		if( key === 0 ){
			lineChartsInstances['session'] = new Chart(lineChartCanvas, args);
		} else if( key === 1 ){
			lineChartsInstances['page_view'] = new Chart(lineChartCanvas, args);
		} else if( key === 2 ){
			lineChartsInstances['bounce_rate'] = new Chart(lineChartCanvas, args);
		}
	});

	// Line chart - Page view per minute / real-time
	const pageViewPerMinute = document.getElementById('page-views-per-minute-chart');
	if( pageViewPerMinute ){
		const reportData = JSON.parse(pageViewPerMinute.dataset.report);

		new Chart(pageViewPerMinute, {
			type: 'line',
			data: {
				labels: reportData.labels,
				datasets: [
					{
						label: 'Page Views',
						data: reportData.values,
						borderColor: '#00a19f',
						pointBackgroundColor: '#00a19f',
					}
				]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
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
						display: false,
					},
				}
			}
		});
	}

	// Pie Charts
	const optionsPie = {
		responsive: true,
		plugins: {
			legend: {
				position: 'bottom',
			},
		}
	}

	const pieCharts = document.querySelectorAll('.ht_easy_ga4_pie_chart');
	pieCharts.forEach( function( pieChartCanvas ){
		if( !pieChartCanvas.dataset.report ){
			return;
		}

		const reportData = JSON.parse(pieChartCanvas.dataset.report);
		const labels = reportData.labels;
		const values = reportData.values;

		new Chart(pieChartCanvas, {
			type: 'pie',
			data: {
				labels: labels,
				datasets: [
					{
						label: 'User Types',
						data: values,
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
	});

	/* Compare Function */
	const compare = document.getElementById('ht_easy_ga4_reports_compare_field');

	if(compare){
		compare.addEventListener('change', function() {
			if(this.checked) {
				lineChartsInstances['session'].data.datasets[1].hidden = false
				lineChartsInstances['page_view'].data.datasets[1].hidden = false
				lineChartsInstances['bounce_rate'].data.datasets[1].hidden = false
			} else {
				lineChartsInstances['session'].data.datasets[1].hidden = true
				lineChartsInstances['page_view'].data.datasets[1].hidden = true
				lineChartsInstances['bounce_rate'].data.datasets[1].hidden = true
			}
			
			lineChartsInstances['session'].update()
			lineChartsInstances['page_view'].update()
			lineChartsInstances['bounce_rate'].update()
		})
	}

} )( jQuery );