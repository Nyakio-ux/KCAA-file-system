<h2>Revision Required: {file_name}</h2>
<p>Hello {recipient_name},</p>

<p>The file <strong>{file_name}</strong> requires revisions.</p>

<p><strong>Status:</strong> Revision Required<br>
<strong>Requested by:</strong> {changed_by}<br>
<strong>Department:</strong> {department}<br>
<strong>Date:</strong> {change_date}</p>

{if comments}
<div style="background:#fff8e6; padding:10px; margin:10px 0; border-radius:4px; border-left:4px solid #f6c23e;">
    <strong>Revision Notes:</strong><br>
    {comments}
</div>
{/if}

<a href="{file_link}" style="background:#f6c23e; color:#000; padding:8px 12px; text-decoration:none; border-radius:4px;">
    View File and Make Revisions
</a>