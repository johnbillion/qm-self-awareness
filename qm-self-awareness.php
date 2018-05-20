<?php
/**
 * Self-Awareness plugin for Query Monitor
 *
 * @package   qm-self-awareness
 * @link      https://github.com/johnbillion/qm-self-awareness
 * @author    John Blackbourn <john@johnblackbourn.com>
 * @copyright 2009-2018 John Blackbourn
 * @license   GPL v2 or later
 *
 * Plugin Name:  Query Monitor Self Awareness
 * Description:  Self-profiling plugin for Query Monitor.
 * Version:      1.0.0
 * Plugin URI:   https://github.com/johnbillion/qm-self-awareness
 * Author:       John Blackbourn
 * Text Domain:  qm-self-awareness
 * Domain Path:  /languages/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

add_action( 'qm/output/after', function( QM_Dispatcher $dispatcher, array $outputters ) {

	echo '<div class="qm" id="qm-self">';
	echo '<table>';
	echo '<thead>';
	echo '<tr>';
	echo '<th>' . esc_html__( 'Panel', 'qm-self-awareness' ) . '</th>';
	echo '<th class="qm-num">' . esc_html_x( 'Data&nbsp;kB', 'kilobytes', 'qm-self-awareness' ) . '</th>';
	echo '<th class="qm-num">' . esc_html_x( 'Proc&nbsp;ms', 'milliseconds', 'qm-self-awareness' ) . '</th>';
	echo '<th class="qm-num">' . esc_html_x( 'Proc&nbsp;kB', 'kilobytes', 'qm-self-awareness' ) . '</th>';
	echo '<th class="qm-num">' . esc_html_x( 'Out&nbsp;ms', 'milliseconds', 'qm-self-awareness' ) . '</th>';
	echo '<th class="qm-num">' . esc_html_x( 'Out&nbsp;kB', 'kilobytes', 'qm-self-awareness' ) . '</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	$total_time = $total_memory = array(
		'data'       => 0,
		'processing' => 0,
		'output'     => 0,
	);

	foreach ( $outputters as $outputter ) {
		$collector       = $outputter->get_collector();
		$collector_timer = $collector->get_timer();
		$output_timer    = $outputter->get_timer();

		$processing_time = $collector_timer->get_time();
		$total_time['processing'] += $processing_time;

		$processing_memory = $collector_timer->get_memory();
		$total_memory['processing'] += $processing_memory;

		$output_time = $output_timer->get_time();
		$total_time['output'] += $output_time;

		$output_memory = $output_timer->get_memory();
		$total_memory['output'] += $output_memory;

		if ( $collector instanceof QM_Collector_Debug_Bar ) {
			$data_kb = '-';
		} else {
			$data_size = $dispatcher::size( $collector->get_data() );

			if ( $data_size instanceof Exception ) {
				$data_kb = $data_size->getMessage();
			} elseif ( ! is_numeric( $data_size ) ) {
				$data_kb = $data_size;
			} else {
				$total_memory['data'] += $data_size;
				$data_kb = number_format_i18n( $data_size / 1024, 1 );
			}
		}

		echo '<tr>';
		echo '<td>' . esc_html( $collector->name() ) . '</td>';
		echo '<td class="qm-num">' . esc_html( $data_kb ) . '</td>';
		echo '<td class="qm-num">' . esc_html( number_format_i18n( $processing_time * 1000, 1 ) ) . '</td>';
		echo '<td class="qm-num">' . esc_html( number_format_i18n( $processing_memory / 1024, 1 ) ) . '</td>';
		echo '<td class="qm-num">' . esc_html( number_format_i18n( $output_time * 1000, 1 ) ) . '</td>';
		echo '<td class="qm-num">' . esc_html( number_format_i18n( $output_memory / 1024, 1 ) ) . '</td>';
		echo '</tr>';
	}

	echo '</tbody>';

	echo '<tfoot>';
	echo '<tr>';
	echo '<td style="text-align:right !important">' . esc_html__( 'Total', 'qm-self-awareness' ) . '</td>';
	echo '<td class="qm-num">' . esc_html( number_format_i18n( $total_memory['data'] / 1024, 1 ) ) . '</td>';
	echo '<td class="qm-num">' . esc_html( number_format_i18n( $total_time['processing'] * 1000, 1 ) ) . '</td>';
	echo '<td class="qm-num">' . esc_html( number_format_i18n( $total_memory['processing'] / 1024, 1 ) ) . '</td>';
	echo '<td class="qm-num">' . esc_html( number_format_i18n( $total_time['output'] * 1000, 1 ) ) . '</td>';
	echo '<td class="qm-num">' . esc_html( number_format_i18n( $total_memory['output'] / 1024, 1 ) ) . '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td colspan="6" style="text-align:right !important" class="qm-info"><em>' . esc_html__( 'Note: A negative value for "Processing kB" means data was filtered out during processing', 'qm-self-awareness' ) . '</em></td>';
	echo '</tr>';
	echo '</tfoot>';

	echo '</table>';
	echo '</div>';

}, 10, 2 );

add_filter( 'qm/output/menus', function( array $menu ) {
	$menu[] = array(
		'title' => 'Self Awareness',
		'id'    => 'query-monitor-conditionals-self-awareness',
		'href'  => '#qm-self',
	);

	return $menu;
}, 99999 );
