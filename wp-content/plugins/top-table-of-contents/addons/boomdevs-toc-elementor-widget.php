<?php

class Boomdevs_Toc_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'boomdevs-toc-widget';
    }

    public function get_title() {
        return esc_html__('Top Table of Contents', 'boomdevs-toc');
    }

    public function get_icon() {
        return 'eicon-table-of-contents';
    }

    public function get_categories() {
        return ['basic'];
    }

    public function get_keywords() {
        return ['table-of-contents', 'toc'];
    }

	protected function register_controls() {

		$this->start_controls_section(
			'section_title',
			[
				'label' => esc_html__('Top Table of Contents', 'boomdevs-toc'),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'shortcode_switch',
			[
				'label'        => esc_html__('Top Table of Contents Switch', 'boomdevs-toc'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('On', 'boomdevs-toc'),
				'label_off'    => esc_html__('Off', 'boomdevs-toc'),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
	
		$this->end_controls_section();
	}
	

    protected function render() {

		$shortcode_switch = $this->get_settings('shortcode_switch');

		if ($shortcode_switch === 'yes') {
			echo do_shortcode('[boomdevs_toc]');
		}
    }
}
