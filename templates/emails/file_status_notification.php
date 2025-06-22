<h2>File Status Changed: {status}</h2>
<p>Hello {recipient_name},</p>
<p>The status of file "{file_name}" has been changed to <strong>{status}</strong> by {changed_by}.</p>

<p><strong>Change Date:</strong> {change_date}</p>

{if comments}
<p><strong>Comments:</strong><br>
{comments}</p>
{/if}

<p><a href="{file_link}">View the file</a></p>