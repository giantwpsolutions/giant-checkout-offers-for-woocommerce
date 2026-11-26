<?php
/**
 * Order Bump Elementor Widget.
 *
 * Renders a live preview of a configured order bump directly on any page or
 * template built with Elementor.  The widget editor exposes every style
 * property that the bump engine already supports so the design looks exactly
 * the same as it will on the checkout / cart page.
 *
 * @package GiantCheckoutOffers\Frontend\Elementor\Widgets\OrderBump
 */

namespace GiantCheckoutOffers\Frontend\Elementor\Widgets\OrderBump;

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

/**
 * Class Main
 *
 * Elementor widget that renders a fully-styled order bump card.
 * Content controls let the editor choose which active bump to display (or
 * enter sample content) and style controls map 1-to-1 with the bump engine's
 * style properties.
 */
class Main extends Widget_Base {

	// ── Identity ─────────────────────────────────────────────────────────────

	public function get_name(): string {
		return 'gcow-order-bump';
	}

	public function get_title(): string {
		return esc_html__( 'Order Bump', 'giant-checkout-offers-for-woocommerce' );
	}

	public function get_icon(): string {
		return 'eicon-woo-add-to-cart';
	}

	public function get_categories(): array {
		return [ 'gcow-category' ];
	}

	public function get_keywords(): array {
		return [ 'order bump', 'upsell', 'woocommerce', 'checkout', 'giant', 'gcow' ];
	}

	public function get_style_depends(): array {
		return [ 'gcow-elementor-widget' ];
	}

	// ── Controls ─────────────────────────────────────────────────────────────

	protected function register_controls(): void {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	// ─── Content tab ─────────────────────────────────────────────────────────

	private function register_content_controls(): void {

		// ── Section: Template ──────────────────────────────────────────────
		$this->start_controls_section(
			'section_template',
			[
				'label' => esc_html__( 'Template', 'giant-checkout-offers-for-woocommerce' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'template',
			[
				'label'   => esc_html__( 'Bump Template', 'giant-checkout-offers-for-woocommerce' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'standard',
				'options' => [
					'standard' => esc_html__( 'Standard',       'giant-checkout-offers-for-woocommerce' ),
					'compact'  => esc_html__( 'Compact',        'giant-checkout-offers-for-woocommerce' ),
					'social'   => esc_html__( 'Social Proof',   'giant-checkout-offers-for-woocommerce' ),
					'split'    => esc_html__( 'Split View',     'giant-checkout-offers-for-woocommerce' ),
					'hero'     => esc_html__( 'Hero Card',      'giant-checkout-offers-for-woocommerce' ),
					'ribbon'   => esc_html__( 'Ribbon Banner',  'giant-checkout-offers-for-woocommerce' ),
					'floating' => esc_html__( 'Floating Card',  'giant-checkout-offers-for-woocommerce' ),
					'timeline' => esc_html__( 'Timeline Offer', 'giant-checkout-offers-for-woocommerce' ),
				],
			]
		);

		$this->end_controls_section();

		// ── Section: Product / Content ──────────────────────────────────────
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'giant-checkout-offers-for-woocommerce' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'bump_title',
			[
				'label'       => esc_html__( 'Title', 'giant-checkout-offers-for-woocommerce' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Special One-Time Offer!', 'giant-checkout-offers-for-woocommerce' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'bump_description',
			[
				'label'   => esc_html__( 'Description', 'giant-checkout-offers-for-woocommerce' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Add this exclusive deal to your order right now.', 'giant-checkout-offers-for-woocommerce' ),
				'rows'    => 3,
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'bump_image',
			[
				'label'   => esc_html__( 'Product Image', 'giant-checkout-offers-for-woocommerce' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'bump_regular_price',
			[
				'label'   => esc_html__( 'Regular Price', 'giant-checkout-offers-for-woocommerce' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '$29.00',
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'bump_discounted_price',
			[
				'label'   => esc_html__( 'Discounted Price', 'giant-checkout-offers-for-woocommerce' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '$19.00',
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'bump_saved_price',
			[
				'label'   => esc_html__( 'Amount Saved (shown in badges / ribbons)', 'giant-checkout-offers-for-woocommerce' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '$10.00',
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'bump_button_text',
			[
				'label'   => esc_html__( 'Button / CTA Text', 'giant-checkout-offers-for-woocommerce' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Yes, Add to Order!', 'giant-checkout-offers-for-woocommerce' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'show_discount',
			[
				'label'        => esc_html__( 'Show Discount', 'giant-checkout-offers-for-woocommerce' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'giant-checkout-offers-for-woocommerce' ),
				'label_off'    => esc_html__( 'No',  'giant-checkout-offers-for-woocommerce' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Shows the original price crossed out and the discount badge / ribbon.', 'giant-checkout-offers-for-woocommerce' ),
			]
		);

		$this->end_controls_section();
	}

	// ─── Style tab ────────────────────────────────────────────────────────────

	private function register_style_controls(): void {

		// ── Section: Card ──────────────────────────────────────────────────
		$this->start_controls_section(
			'section_style_card',
			[
				'label' => esc_html__( 'Card', 'giant-checkout-offers-for-woocommerce' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'style_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .gcow-el-card',
			]
		);

		$this->add_control(
			'card_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'giant-checkout-offers-for-woocommerce' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 48 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 8 ],
				'selectors'  => [
					'{{WRAPPER}} .gcow-el-card' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label'      => esc_html__( 'Padding', 'giant-checkout-offers-for-woocommerce' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'default'    => [
					'top'      => 12, 'right'  => 12,
					'bottom'   => 12, 'left'   => 12,
					'unit'     => 'px', 'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .gcow-el-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .gcow-el-card',
			]
		);

		$this->end_controls_section();

		// ── Section: Typography ───────────────────────────────────────────
		$this->start_controls_section(
			'section_style_typography',
			[
				'label' => esc_html__( 'Typography', 'giant-checkout-offers-for-woocommerce' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// Title
		$this->add_control(
			'style_text_color',
			[
				'label'     => esc_html__( 'Title Color', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Title Typography', 'giant-checkout-offers-for-woocommerce' ),
				'selector' => '{{WRAPPER}} .gcow-el-title',
			]
		);

		// Description
		$this->add_control(
			'style_description_color',
			[
				'label'     => esc_html__( 'Description Color', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .gcow-el-description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'description_typography',
				'label'    => esc_html__( 'Description Typography', 'giant-checkout-offers-for-woocommerce' ),
				'selector' => '{{WRAPPER}} .gcow-el-description',
			]
		);

		$this->end_controls_section();

		// ── Section: Price ────────────────────────────────────────────────
		$this->start_controls_section(
			'section_style_price',
			[
				'label' => esc_html__( 'Price', 'giant-checkout-offers-for-woocommerce' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'style_price_color',
			[
				'label'     => esc_html__( 'Price Color', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-price'          => 'color: {{VALUE}};',
					'{{WRAPPER}} .gcow-el-discounted-price' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'price_typography',
				'label'    => esc_html__( 'Price Typography', 'giant-checkout-offers-for-woocommerce' ),
				'selector' => '{{WRAPPER}} .gcow-el-price, {{WRAPPER}} .gcow-el-discounted-price',
			]
		);

		$this->end_controls_section();

		// ── Section: Button / CTA ────────────────────────────────────────
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Button / CTA', 'giant-checkout-offers-for-woocommerce' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'style_button_color',
			[
				'label'     => esc_html__( 'Button Background', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-cta-label' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .gcow-el-cta-label .gcow-el-cta-bg' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'style_button_text_color',
			[
				'label'     => esc_html__( 'Button Text Color', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-cta-label .gcow-el-cta-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'label'    => esc_html__( 'Button Typography', 'giant-checkout-offers-for-woocommerce' ),
				'selector' => '{{WRAPPER}} .gcow-el-cta-label .gcow-el-cta-text',
			]
		);

		$this->add_control(
			'style_cta_bg_color',
			[
				'label'     => esc_html__( 'CTA Area Background (Standard / Social / Floating templates)', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .gcow-el-cta-wrap' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'style_cta_border_color',
			[
				'label'     => esc_html__( 'CTA Area Border Color', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-cta-wrap' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ── Section: Header (Standard / Social) ─────────────────────────
		$this->start_controls_section(
			'section_style_header',
			[
				'label'     => esc_html__( 'Header Bar (Standard / Social templates)', 'giant-checkout-offers-for-woocommerce' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'template' => [ 'standard', 'social' ] ],
			]
		);

		$this->add_control(
			'style_header_bg_color',
			[
				'label'     => esc_html__( 'Header Background', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-header' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'style_header_text_color',
			[
				'label'     => esc_html__( 'Header Text Color', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-header'    => 'color: {{VALUE}};',
					'{{WRAPPER}} .gcow-el-header *'  => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ── Section: Ribbon (Ribbon template) ───────────────────────────
		$this->start_controls_section(
			'section_style_ribbon',
			[
				'label'     => esc_html__( 'Ribbon (Ribbon template)', 'giant-checkout-offers-for-woocommerce' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'template' => 'ribbon' ],
			]
		);

		$this->add_control(
			'style_ribbon_color',
			[
				'label'     => esc_html__( 'Ribbon Color', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-ribbon-flag' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'style_accent_color',
			[
				'label'     => esc_html__( 'Accent Color', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-accent' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ── Section: Badge (Floating template) ──────────────────────────
		$this->start_controls_section(
			'section_style_badge',
			[
				'label'     => esc_html__( 'Badge (Floating template)', 'giant-checkout-offers-for-woocommerce' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'template' => 'floating' ],
			]
		);

		$this->add_control(
			'style_badge_color',
			[
				'label'     => esc_html__( 'Badge Background', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-badge' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'style_badge_text_color',
			[
				'label'     => esc_html__( 'Badge Text Color', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-badge' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ── Section: Toggle / Stars (Split template) ─────────────────────
		$this->start_controls_section(
			'section_style_split',
			[
				'label'     => esc_html__( 'Toggle & Stars (Split template)', 'giant-checkout-offers-for-woocommerce' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'template' => 'split' ],
			]
		);

		$this->add_control(
			'style_toggle_color',
			[
				'label'     => esc_html__( 'Toggle Color', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-toggle-track' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'style_star_color',
			[
				'label'     => esc_html__( 'Star Color', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-stars svg' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ── Section: Timeline Accent ──────────────────────────────────────
		$this->start_controls_section(
			'section_style_timeline',
			[
				'label'     => esc_html__( 'Accent (Timeline template)', 'giant-checkout-offers-for-woocommerce' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'template' => 'timeline' ],
			]
		);

		$this->add_control(
			'style_timeline_accent_color',
			[
				'label'     => esc_html__( 'Accent Color', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-timeline-step'  => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .gcow-el-timeline-line'  => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .gcow-el-timeline-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ── Section: Save Badge (Compact template) ────────────────────────
		$this->start_controls_section(
			'section_style_compact',
			[
				'label'     => esc_html__( 'Save Badge (Compact template)', 'giant-checkout-offers-for-woocommerce' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'template' => 'compact' ],
			]
		);

		$this->add_control(
			'style_save_badge_color',
			[
				'label'     => esc_html__( 'Save Badge Color', 'giant-checkout-offers-for-woocommerce' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gcow-el-save-badge' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	// ── Render ────────────────────────────────────────────────────────────────

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		include __DIR__ . '/RenderView.php';
	}
}
