<table>
    <thead>
        <tr>
            <th class="debug-bar-width6r">Time</th>
            <th>Query String</th>
            <th>Affected rows</th>
        </tr>
    </thead>
    <tbody>
    {queries}
        <tr>
            <td class="narrow">{duration}</td>
            <td>{! sql !}</td>
            <td>{affected_rows}</td>
        </tr>
    {/queries}
    </tbody>
</table>
