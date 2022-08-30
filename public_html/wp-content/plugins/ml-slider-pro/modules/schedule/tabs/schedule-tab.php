<?php 
	global $wp_locale;
	$current_time = current_time('timestamp');
?>

<div class="row schedule">
	<input 
		class="schedule-slide mr-0"
		type="checkbox" 
		id="schedule-select-<?php echo $post->ID ?>"
		name="attachment[<?php echo $post->ID ?>][schedule]" 
		<?php echo ('yes' === $is_scheduled) ? 'checked="checked"' : ''; ?>
	> 
	<label class="schedule-slide" for="schedule-select-<?php echo $post->ID ?>">
		<?php _e('Schedule this slide', 'ml-slider-pro'); ?>
	</label>
	<div class="relative bg-gray-lightest border border-gray-light border-l-8 rtl:border-r-8 rtl:border-l-0 rtl:pl-0 rtl:pr-4 flex flex-col hide-if-notchecked mt-3 pl-4 py-2">
		<div class="flex flex-nowrap -mx-2">
			<table class="mx-2">
				<thead>
					<tr>
						<th colspan="3"><?php _e('Show slide:'); ?></th>
						<th><?php _e('Hour'); ?></th>
						<th></th>
						<th><?php _e('Minute'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php 
					// Essentially if $schedule_start isn't set, we can set defaults here
					$texts = array(
						'from' => _x('From', 'As in "From January 7th to April 2nd..."', 'ml-slider-pro'),
						'to' => _x('To', 'As in "From January 7th to April 2nd..."', 'ml-slider-pro')
					);
					foreach(array('from' => $schedule_start, 'to' => $schedule_end) as $id => $date) :

						// By default use the current time
						$start_real = $date ? mysql2date('Y-m-d', $date, false) : gmdate('Y-m-d', $current_time);
						$hh = $date ? mysql2date('H', $date, false) : gmdate('H', $current_time);
						$mn = $date ? mysql2date('i', $date, false) : gmdate('i', $current_time);
						$ss = $date ? mysql2date('s', $date, false) : gmdate('s', $current_time);

						// By default select every day ($days_scheduled could be false if coming from an older version)
						$days_scheduled = ($date && $days_scheduled) ? $days_scheduled : array(0, 1, 2, 3, 4, 5, 6);
					?>
					<tr>
						<td class="text-right"><?php echo $texts[$id]; ?></td>
						<td>
							<label class="mx-1 mb-0">
								<span class="screen-reader-text"><?php _e('Date'); ?></span>
								<input
									data-lpignore="true"
									type="text"
									class="datepicker m-0 p-0 px-1 w-auto min-h-0 h-6"
									name="attachment[<?php echo $post->ID; ?>][<?php echo $id; ?>][date]"
									value="<?php echo $start_real; ?>"
								>
							</label>
						</td>
						<td><span class="mx-1"><?php _ex('at', 'As in "your slide will display Tuesday at 5pm"', 'ml-slider-pro'); ?></span></td>
						<td>
							<label class="m-0 ml-1">
								<span class="screen-reader-text text-center"><?php _e('Hour'); ?></span>
								<input
									data-lpignore="true"
									type="text"
									class="m-0 p-0 px-1 w-auto min-h-0 h-6"
									name="attachment[<?php echo $post->ID; ?>][<?php echo $id; ?>][hh]"
									value="<?php echo $hh; ?>"
									size="2"
									maxlength="2"
									autocomplete="off"
								>
							</label>
						</td>
						<td>:</td>
						<td>
							<label class="m-0 text-center">
								<span class="screen-reader-text"><?php _e('Minute'); ?></span>
								<input
									data-lpignore="true"
									type="text"
									class="m-0 p-0 px-1 w-auto min-h-0 h-6"
									name="attachment[<?php echo $post->ID; ?>][<?php echo $id; ?>][mn]"
									value="<?php echo $mn; ?>"
									size="2"
									maxlength="2"
									autocomplete="off"
								>
							</label>
							<input
								type="hidden"
								name="attachment[<?php echo $post->ID; ?>][<?php echo $id; ?>][ss]"
								value="<?php echo $ss; ?>"
							>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<table class="mx-2 days-schedules">
				<thead>
					<tr>
						<th><?php _e('Show on:', 'ml-slider-pro'); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="flex -mx-1">
						<?php
						// Sunday = 0, etc
						foreach(array_values($wp_locale->weekday_abbrev) as $day_id => $day_abbr) :
							$days_scheduled = is_array($days_scheduled) ? $days_scheduled : array(); ?>
							<td class="mt-1 mx-1 flex flex-col items-center justify-center">
								<input 
									type="checkbox"
									class="mx-auto"
									name="attachment[<?php echo $post->ID; ?>][days][<?php echo $day_id; ?>]"
									<?php echo in_array($day_id, $days_scheduled) ? 'checked="checked"' : ''; ?>
								><label><?php echo $day_abbr ?></label>
							</td>
						<?php endforeach; ?>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="mt-2">
			<?php
				// use 00:00 if nothing is set
				echo '</a>';
				$hh_from = $constraint_time_start ? gmdate('H', strtotime($constraint_time_start)) : '00';
				$mn_from = $constraint_time_start ? gmdate('i', strtotime($constraint_time_start)) : '00';
				$hh_until = $constraint_time_end ? gmdate('H', strtotime($constraint_time_end)) : '00';
				$mn_until = $constraint_time_end ? gmdate('i', strtotime($constraint_time_end)) : '00';
			?>
			<table>
				<thead>
					<tr>
						<th colspan="9" class="mb-1">
							<?php _e('Daily constraint:', 'ml-slider-pro'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="9">
							<input 
								type="checkbox"
								class="relative m-0 mr-2 rtl:ml-2 rtl:mr-0"
								style="top:-1px"
								id="all-day-<?php echo $post->ID; ?>"
								name="attachment[<?php echo $post->ID; ?>][all_day]"
								<?php echo !$all_day || filter_var($all_day, FILTER_VALIDATE_BOOLEAN) ? 'checked="checked"' : ''; ?>
							><label
								for="all-day-<?php echo $post->ID; ?>"
								class="tipsy-tooltip-top m-0" title="<?php _e('If checked, the folowing time constraints will be ignored', 'ml-slider-pro'); ?>">
								<?php _e('All day (<span class="text-blue">?</span>)', 'ml-slider-pro'); ?>
							</label>
						</td>
					</tr>
					<tr>
						<td class="text-right"><?php echo $texts['from']; ?></td>
						<td>
							<label class="m-0">
								<span class="screen-reader-text"><?php _e('Hour'); ?></span>
								<input
									data-lpignore="true"
									type="text"
									class="m-0 ml-1 p-0 px-1 w-auto min-h-0 h-6"
									name="attachment[<?php echo $post->ID; ?>][constraint_from][hh]"
									value="<?php echo $hh_from; ?>"
									size="2"
									maxlength="2"
									autocomplete="off"
								>
							</label>
						</td>
						<td>:</td>
						<td>
							<label class="m-0">
								<span class="screen-reader-text"><?php _e('Minute'); ?></span>
								<input
									data-lpignore="true"
									type="text"
									class="m-0 p-0 px-1 w-auto min-h-0 h-6"
									name="attachment[<?php echo $post->ID; ?>][constraint_from][mn]"
									value="<?php echo $mn_from; ?>"
									size="2"
									maxlength="2"
									autocomplete="off"
								>
							</label>
						</td>
						<td><span class="mx-1 px-1"><?php _ex('until', 'As in "1:00 until 2:30..."', 'ml-slider-pro') ?></span></td>
						<td>
							<label class="m-0">
								<span class="screen-reader-text"><?php _e('Hour'); ?></span>
								<input
									data-lpignore="true"
									type="text"
									class="m-0 p-0 px-1 w-auto min-h-0 h-6"
									name="attachment[<?php echo $post->ID; ?>][constraint_to][hh]"
									value="<?php echo $hh_until; ?>"
									size="2"
									maxlength="2"
									autocomplete="off"
								>
							</label>
						</td>
						<td>:</td>
						<td>
							<label class="m-0">
								<span class="screen-reader-text"><?php _e('Minute'); ?></span>
								<input
									data-lpignore="true"
									type="text"
									class="m-0 p-0 px-1 w-auto min-h-0 h-6"
									name="attachment[<?php echo $post->ID; ?>][constraint_to][mn]"
									value="<?php echo $mn_until; ?>"
									size="2"
									maxlength="2"
									autocomplete="off"
								>
							</label>
						</td>
						<td>
							<input 
								type="checkbox"
								style="top:-1px"
								id="show-hide-<?php echo $post->ID; ?>"
								name="attachment[<?php echo $post->ID; ?>][show_during_constraint]"
								<?php echo !$constraint_time_show || filter_var($constraint_time_show, FILTER_VALIDATE_BOOLEAN) ? 'checked="checked"' : ''; ?>
								class="relative m-0 ml-2 rtl:ml-0 rtl:mr-2" 
							>
							<label
								for="show-hide-<?php echo $post->ID; ?>"
								class="relative tipsy-tooltip-top m-0"
								title="<?php _e('If unchecked, the slide will be hidden during this time constraint', 'ml-slider-pro'); ?>"
								>
								<?php _e('Show (<span class="text-blue">?</span>)', 'ml-slider-pro'); ?>
							</label>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<span class="tipsy-tooltip-top ms-time-helper absolute bottom-0 m-1 right-0 rtl:left-0 rtl:right-auto text-gray tipsy-tooltip-top" data-time="<?php echo gmdate('Y-m-d h:i:s', $current_time); ?>" data-now-text="<?php _e('Current server time', 'ml-slider-pro') ?>"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
	</div>
</div>
