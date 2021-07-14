<p class="debug-bar-alignRight">
	<a href="http://dframework.totalh.net/" target="_blank" >Read the dFramework docs...</a>
</p>

<table>
	<tbody>
		<tr>
			<td>dFramework Version:</td>
			<td>{ dFrameworkVersion }</td>
		</tr>
		<tr>
			<td>PHP Version:</td>
			<td>{ phpVersion }</td>
		</tr>
		<tr>
			<td>Server Version:</td>
			<td>{ serverVersion }</td>
		</tr>
		<tr>
			<td>OS:</td>
			<td>{ os }</td>
		</tr>
		<tr>
			<td>PHP SAPI:</td>
			<td>{ phpSAPI }</td>
		</tr>
		<tr>
			<td>Environment:</td>
			<td>{ environment }</td>
		</tr>
		<tr>
			<td>Base URL:</td>
			<td>
				{ if $baseURL == '' }
					<div class="warning">
						The $baseURL should always be set manually to prevent possible URL personification from external parties.
					</div>
				{ else }
					{ baseURL }
				{ endif }
			</td>
		</tr>
		<tr>
			<td>Document Root:</td>
			<td>{ documentRoot }</td>
		</tr>
		<tr>
			<td>TimeZone:</td>
			<td>{ timezone }</td>
		</tr>
		<tr>
			<td>Locale:</td>
			<td>{ locale }</td>
		</tr>
	</tbody>
</table>
