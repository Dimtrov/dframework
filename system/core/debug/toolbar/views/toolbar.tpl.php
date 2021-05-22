<?php
/**
 * @var \dFramework\core\debug\Toolbar $this
 * @var int                        $totalTime
 * @var int                        $totalMemory
 * @var string                     $url
 * @var string                     $method
 * @var bool                       $isAJAX
 * @var int                        $startTime
 * @var int                        $totalTime
 * @var int                        $totalMemory
 * @var float                      $segmentDuration
 * @var int                        $segmentCount
 * @var string                     $dF_VERSION
 * @var array                      $collectors
 * @var array                      $vars
 * @var array                      $styles
 * @var \dFramework\core\output\Parser   $parser
 */

?>
<style type="text/css"><?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . '/toolbar.css')) ?></style>
<script type="text/javascript"><?= file_get_contents(__DIR__ . '/toolbar-min.js') ?></script>

<div id="debug-icon" class="debug-bar-ndisplay">
	<a id="debug-icon-link" href="javascript:void(0)">
		<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
			x="0px" y="0px" width="200px" height="200px" viewBox="0 0 200 200" preserveAspectRatio="xMidYMid meet" enable-background="new 0 0 175 173" xml:space="preserve">
			<image width="200" height="200" x="0" y="0" xlink:href="data:image/png;base64,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" />
		</svg>
	</a>
</div>
<div id="debug-bar">
	<div class="toolbar">
		<span id="toolbar-position"><a href="javascript: void(0)">&#8597;</a></span>
		<span id="toolbar-theme"><a href="javascript: void(0)">&#128261;</a></span>
		<span class="ci-label">
			<a href="javascript: void(0)" data-tab="ci-timeline">
				<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAD7SURBVEhLY6ArSEtLK09NTbWHcvGC9PR0BaDaQiAdUl9fzwQVxg+AFvwHamqHcnGCpKQkeaDa9yD1UD09UCn8AKaBWJySkmIApFehi0ONwwRQBceBLurAh4FqFoHUAtkrgPgREN+ByYEw1DhMANVEMIhAYQ5U1wtU/wmILwLZRlAp/IBYC8gGw88CaFj3A/FnIL4ETDXGUCnyANSC/UC6HIpnQMXAqQXIvo0khxNDjcMEQEmU9AzDuNI7Lgw1DhOAJIEuhQcRKMcC+e+QNHdDpcgD6BaAANSSQqBcENFlDi6AzQKqgkFlwWhxjVI8o2OgmkFaXI8CTMDAAAAxd1O4FzLMaAAAAABJRU5ErkJggg==">
				<span class="hide-sm"><?= $totalTime ?> ms &nbsp; <?= $totalMemory ?> MB</span>
			</a>
		</span>

		<?php foreach ($collectors as $c) : ?>
			<?php if (! $c['isEmpty'] && ($c['hasTabContent'] || $c['hasLabel'])) : ?>
				<span class="ci-label">
					<a href="javascript: void(0)" data-tab="ci-<?= $c['titleSafe'] ?>">
						<img src="<?= $c['icon'] ?>">
						<span class="hide-sm">
							<?= $c['title'] ?>
							<?php if (! is_null($c['badgeValue'])) : ?>
								<span class="badge"><?= $c['badgeValue'] ?></span>
							<?php endif ?>
						</span>
					</a>
				</span>
			<?php endif ?>
		<?php endforeach ?>

		<span class="ci-label">
			<a href="javascript: void(0)" data-tab="ci-vars">
				<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACLSURBVEhLYxgFJIHU1NSraWlp/6H4T0pKSjRUijoAyXAwBlrYDpViAFpmARQrJwZDtWACoCROC4D8CnR5XBiqBRMADfyNprgRKkUdAApzoCUdUNwE5MtApYYIALp6NBWBMVQLJgAaOJqK8AOgq+mSio6DggjEBtLUT0UwQ5HZIADkj6aiUTAggIEBANAEDa/lkCRlAAAAAElFTkSuQmCC">
				<span class="hide-sm">Vars</span>
			</a>
		</span>

		<h1>
			<span class="ci-label">
				<a href="javascript: void(0)" data-tab="ci-config">
					<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="25px" height="25px" viewBox="0 -6 20 20" preserveAspectRatio="xMidYMid meet">
						<image width="15" height="15" x="0" y="0" xlink:href="data:image/png;base64,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" />
					</svg>

					<?= $dF_VERSION ?>
				</a>
			</span>
		</h1>

		<!-- Open/Close Toggle -->
		<a id="debug-bar-link" href="javascript:void(0)" title="Open/Close">
			<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAEPSURBVEhL7ZVLDoJAEEThRuoGDwSEG+jCuFU34s3AK3APP1VDDSGMqI1xx0s6M/2rnlHEaMZElmWrPM+vsDvsYbQ7+us0TReSC2EBrEHxCevRYuppYLXkQpC8sVCuGfTvqSE3hFdFwUGuGfRvqSE35NUAfKZrbQNQm2jrMA+gOK+M+FmhDsRL5voHMA8gFGecq0JOXLWlQg7E7AMIxZnjOiZOEJ82gFCcedUE4gS56QP8yf8ywItz7e+RituKlkkDBoIOH4Nd4HZD4NsGYJ/Abn1xEVOcuZ8f0zc/tHiYmzTAwscBvDIK/veyQ9K/rnewjdF26q0kF1IUxZIFPAVW98x/a+qp8L2M/+HMhETRE6S8TxpZ7KGXAAAAAElFTkSuQmCC">
		</a>
	</div>

	<!-- Timeline -->
	<div id="ci-timeline" class="tab">
		<table class="timeline">
			<thead>
			<tr>
				<th class="debug-bar-width30">NAME</th>
				<th class="debug-bar-width10">COMPONENT</th>
				<th class="debug-bar-width10">DURATION</th>
				<?php for ($i = 0; $i < $segmentCount; $i++) : ?>
					<th><?= $i * $segmentDuration ?> ms</th>
				<?php endfor ?>
			</tr>
			</thead>
			<tbody>
			<?= $this->renderTimeline($collectors, $startTime, $segmentCount, $segmentDuration,
				$styles) ?>
			</tbody>
		</table>
	</div>

	<!-- Collector-provided Tabs -->
	<?php foreach ($collectors as $c) : ?>
		<?php if (! $c['isEmpty']) : ?>
			<?php if ($c['hasTabContent']) : ?>
				<div id="ci-<?= $c['titleSafe'] ?>" class="tab">
					<h2><?= $c['title'] ?> <span><?= $c['titleDetails'] ?></span></h2>

					<?= is_string($c['display']) ? $c['display'] : $parser->setData($c['display'])->display("_{$c['titleSafe']}.tpl")->render(); ?>
				</div>
			<?php endif ?>
		<?php endif ?>
	<?php endforeach ?>

	<!-- In & Out -->
	<div id="ci-vars" class="tab">

		<!-- VarData from Collectors -->
		<?php if (isset($vars['varData'])) : ?>
			<?php foreach ($vars['varData'] as $heading => $items) : ?>

				<a href="javascript:void(0)" onclick="ciDebugBar.toggleDataTable('<?= strtolower(str_replace(' ',
					'-', $heading)) ?>'); return false;">
					<h2><?= $heading ?></h2>
				</a>

				<?php if (is_array($items)) : ?>

					<table id="<?= strtolower(str_replace(' ', '-', $heading . '_table')) ?>">
						<tbody>
						<?php foreach ($items as $key => $value) : ?>
							<tr>
								<td><?= $key ?></td>
								<td><?= $value ?></td>
							</tr>
						<?php endforeach ?>
						</tbody>
					</table>

				<?php else: ?>
					<p class="muted">No data to display.</p>
				<?php endif ?>
			<?php endforeach ?>
		<?php endif ?>

		<!-- Session -->
		<a href="javascript:void(0)" onclick="ciDebugBar.toggleDataTable('session'); return false;">
			<h2>Session User Data</h2>
		</a>

		<?php if (isset($vars['session'])) : ?>
			<?php if (! empty($vars['session'])) : ?>
				<table id="session_table">
					<tbody>
					<?php foreach ($vars['session'] as $key => $value) : ?>
						<tr>
							<td><?= $key ?></td>
							<td><?= $value ?></td>
						</tr>
					<?php endforeach ?>
					</tbody>
				</table>
			<?php else : ?>
				<p class="muted">No data to display.</p>
			<?php endif ?>
		<?php else : ?>
			<p class="muted">Session doesn't seem to be active.</p>
		<?php endif ?>

		<h2>Request <span>( <?= $vars['request'] ?> )</span></h2>

		<?php if (isset($vars['get']) && $get = $vars['get']) : ?>
			<a href="javascript:void(0)" onclick="ciDebugBar.toggleDataTable('get'); return false;">
				<h3>$_GET</h3>
			</a>

			<table id="get_table">
				<tbody>
				<?php foreach ($get as $name => $value) : ?>
					<tr>
						<td><?= $name ?></td>
						<td><?= $value ?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>

		<?php if (isset($vars['post']) && $post = $vars['post']) : ?>
			<a href="javascript:void(0)" onclick="ciDebugBar.toggleDataTable('post'); return false;">
				<h3>$_POST</h3>
			</a>

			<table id="post_table">
				<tbody>
				<?php foreach ($post as $name => $value) : ?>
					<tr>
						<td><?= $name ?></td>
						<td><?= $value ?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>

		<?php if (isset($vars['headers']) && $headers = $vars['headers']) : ?>
			<a href="javascript:void(0)" onclick="ciDebugBar.toggleDataTable('request_headers'); return false;">
				<h3>Headers</h3>
			</a>

			<table id="request_headers_table">
				<tbody>
				<?php foreach ($headers as $header => $value) : ?>
					<tr>
						<td><?= $header ?></td>
						<td><?= $value ?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>

		<?php if (isset($vars['cookies']) && $cookies = $vars['cookies']) : ?>
			<a href="javascript:void(0)" onclick="ciDebugBar.toggleDataTable('cookie'); return false;">
				<h3>Cookies</h3>
			</a>

			<table id="cookie_table">
				<tbody>
				<?php foreach ($cookies as $name => $value) : ?>
					<tr>
						<td><?= $name ?></td>
						<td><?= is_array($value) ? print_r($value, true) : $value ?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>

		<h2>Response
			<span>( <?= $vars['response']['statusCode'] . ' - ' . $vars['response']['reason'] ?> )</span>
		</h2>

		<?php if (isset($vars['headers']) && $headers = $vars['headers']) : ?>
			<a href="javascript:void(0)" onclick="ciDebugBar.toggleDataTable('response_headers'); return false;">
				<h3>Headers</h3>
			</a>

			<table id="response_headers_table">
				<tbody>
				<?php foreach ($headers as $header => $value) : ?>
					<tr>
						<td><?= $header ?></td>
						<td><?= $value ?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>
	</div>

	<!-- Config Values -->
	<div id="ci-config" class="tab">
		<h2>System Configuration</h2>

		<?= $parser->setData($config)->display('_config.tpl')->render(); ?>
	</div>
</div>
<style type="text/css">
	<?php foreach($styles as $name => $style) : ?>
	.<?= $name ?> {
		<?= $style ?>
	}

	<?php endforeach ?>
</style>
