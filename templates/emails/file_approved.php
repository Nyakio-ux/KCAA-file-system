<h2>File Approved: {file_name}</h2>
<p>Hello {recipient_name},</p>

<p>The file <strong>{file_name}</strong> has been <strong>approved</strong> by {changed_by}.</p>

<p><strong>Department:</strong> {department}<br>
<strong>Approved on:</strong> {change_date}</p>

{if comments}
<div style="background:#f5f5f5; padding:10px; margin:10px 0; border-radius:4px;">
    <strong>Approver Comments:</strong><br>
    {comments}
</div>
{/if}

<a href="{file_link}" style="background:#4e73df; color:white; padding:8px 12px; text-decoration:none; border-radius:4px;">
    View Approved File
</a>