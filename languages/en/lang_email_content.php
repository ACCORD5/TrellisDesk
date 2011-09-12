<?php

/*
#======================================================
|    | Trellis Desk Language File
|    | lang_email_content.php
#======================================================
*/

$lang['header'] = <<<EOF
Dear {USER_NAME},
EOF;

$lang['header_html'] = <<<EOF
&lt;p&gt;Dear {USER_NAME},&lt;/p&gt;
EOF;

$lang['footer'] = <<<EOF
Regards,



The {TD_NAME} team.

{TD_URL}
EOF;

$lang['footer_html'] = <<<EOF
&lt;p&gt;Regards,&lt;/p&gt;

&lt;p&gt;The {TD_NAME} team.&lt;br /&gt;&lt;a href=&quot;{TD_URL}&quot;&gt;{TD_URL}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['user_new_val_email_sub'] = "Verify Your Email";

$lang['user_new_val_email'] = <<<EOF
Welcome to {TD_NAME}.  You have created a new user account at our help desk system.  In order to activate your account, you must verify this email address by clicking the verification link below.



---------------------------



Verification Link: {LINK}



---------------------------



Once you have verified this email address, you will be able to log in.
EOF;

$lang['user_new_val_email_html'] = <<<EOF
&lt;p&gt;Welcome to {TD_NAME}.&amp;nbsp; You have created a new user account at our help desk system.&amp;nbsp; In order to activate your account, you must verify this email address by clicking the verification link below.&lt;/p&gt;

&lt;p&gt;---------------------------&lt;/p&gt;

&lt;p&gt;Verification Link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;

&lt;p&gt;---------------------------&lt;/p&gt;

&lt;p&gt;Once you have verified this email address, you will be able to log in.&lt;/p&gt;
EOF;

$lang['user_new_val_admin_sub'] = "New User Account Pending";

$lang['user_new_val_admin'] = <<<EOF
Welcome to {TD_NAME}.  You have created a new user account at our help desk system.  Before you can begin using your account, an administrator must manually approve your account.  You will receive an email when your account is approved.
EOF;

$lang['user_new_val_admin_html'] = <<<EOF
&lt;p&gt;Welcome to {TD_NAME}.&amp;nbsp; You have created a new user account at our help desk system.&amp;nbsp; Before you can begin using your account, an administrator must manually approve your account.&amp;nbsp; You will receive an email when your account is approved.&lt;/p&gt;
EOF;

$lang['user_new_val_admin_staff_sub'] = "New Registration: {UNAME}";

$lang['user_new_val_admin_staff'] = <<<EOF
A new user has registered as is awaiting approval.

---------------------------

User: {UNAME}
Email: {UEMAIL}

---------------------------

You can manage users awaiting approval using this link: {LINK}
EOF;

$lang['user_new_val_admin_staff_html'] = <<<EOF
&lt;p&gt;A new user has registered as is awaiting approval.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;User: {UNAME}&lt;br /&gt;Email: {UEMAIL}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can manage users awaiting approval using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['user_new_val_both_sub'] = "Verify Your Email";

$lang['user_new_val_both'] = <<<EOF
Welcome to {TD_NAME}.  You have created a new user account at our help desk system.  In order to activate your account, you must verify this email address by clicking the validation link below.  Additionally, an administrator must also manually approve your account.

---------------------------

Validation Link: {LINK}

---------------------------

You will receive an email when an administrator approves your account.
EOF;

$lang['user_new_val_both_html'] = <<<EOF
&lt;p&gt;Welcome to {TD_NAME}.&amp;nbsp; You have created a new user account at our help desk system.&amp;nbsp; In order to activate your account, you must verify this email address by clicking the validation link below.&amp;nbsp; Additionally, an administrator must also manually approve your account.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Validation Link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You will receive an email when an administrator approves your account.&lt;/p&gt;
EOF;

$lang['user_almost_activated_sub'] = "Awaiting Email Verification";

$lang['user_almost_activated'] = <<<EOF
Your user account has been approved by an administrator.  But before you can begin using your account, you must first click the email verification link in the email that was earlier sent to your email address.
EOF;

$lang['user_almost_activated_html'] = <<<EOF
&lt;p&gt;Your user account has been approved by an administrator.&amp;nbsp; But before you can begin using your account, you must first click the email verification link in the email that was earlier sent to your email address.&lt;/p&gt;
EOF;

$lang['user_almost_approved_sub'] = "Awaiting Admin Approval";

$lang['user_almost_approved'] = <<<EOF
Your email address has been verified.  But before you can begin using your account, an administrator must manually approve your user account.  You will receive an email when your account is approved.
EOF;

$lang['user_almost_approved_html'] = <<<EOF
&lt;p&gt;Your email address has been verified.&amp;nbsp; But before you can begin using your account, an administrator must manually approve your user account.&amp;nbsp; You will receive an email when your account is approved.&lt;/p&gt;
EOF;

$lang['user_activated_sub'] = "Email Verified";

$lang['user_activated'] = <<<EOF
Your email address has been verified and your user account has been activated.  You may now log in.
EOF;

$lang['user_activated_html'] = <<<EOF
&lt;p&gt;Your email address has been verified and your user account has been activated.&amp;nbsp; You may now log in.&lt;/p&gt;
EOF;

$lang['user_approved_sub'] = "Account Approved";

$lang['user_approved'] = <<<EOF
Your user account has been approved by an administrator.  You may now login.
EOF;

$lang['user_approved_html'] = <<<EOF
Your user account has been approved by an administrator.&amp;nbsp; You may now login.
EOF;

$lang['change_email_val_sub'] = "Verify Your Email";

$lang['change_email_val'] = <<<EOF
You have requested for your email to be changed to this address.  In order to update your email, you must verify this address by clicking the verification link below.

---------------------------

Verification Link: {LINK}

---------------------------
EOF;

$lang['change_email_val_html'] = <<<EOF
&lt;p&gt;You have requested for your email to be changed to this address.&amp;nbsp; In order to update your email, you must verify this address by clicking the verification link below.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Verification Link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
EOF;

$lang['reset_pass_val_sub'] = "Reset Password";

$lang['reset_pass_val'] = <<<EOF
You have requested to reset your password at {TD_NAME}.  To reset your password, click the verification link below.  If you did not request to reset your password, please disregard this email.

---------------------------

Verification Link: {LINK}

---------------------------
EOF;

$lang['reset_pass_val_html'] = <<<EOF
&lt;p&gt;You have requested to reset your password at {TD_NAME}.&amp;nbsp; To reset your password, click the verification link below.&amp;nbsp; If you did not request to reset your password, please disregard this email.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Verification Link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
EOF;

$lang['ticket_new_sub'] = "Ticket ID #{TICKET_ID}";

$lang['ticket_new'] = <<<EOF
You have submitted a new ticket.  Our staff will review your ticket shortly and reply accordingly.

---------------------------

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
Department: {DEPARTMENT}
Priority: {PRIORITY}

---------------------------

You can view your ticket using this link: {LINK}
EOF;

$lang['ticket_new_html'] = <<<EOF
&lt;p&gt;You have submitted a new ticket.&amp;nbsp; Our staff will review your ticket shortly and reply accordingly.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt;Subject: {SUBJECT}&lt;br /&gt;Department: {DEPARTMENT}&lt;br /&gt;Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can view your ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_new_behalf_sub'] = "Ticket ID #{TICKET_ID}";

$lang['ticket_new_behalf'] = <<<EOF
A new ticket has been submitted in your behalf.  Our staff will review your ticket shortly and reply accordingly.

---------------------------

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
Department: {DEPARTMENT}
Priority: {PRIORITY}

---------------------------

{MESSAGE}

---------------------------

You can view your ticket using this link: {LINK}
EOF;

$lang['ticket_new_behalf_html'] = <<<EOF
&lt;p&gt;A new ticket has been submitted in your behalf.&amp;nbsp; Our staff will review your ticket shortly and reply accordingly.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt;Subject: {SUBJECT}&lt;br /&gt;Department: {DEPARTMENT}&lt;br /&gt;Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;{MESSAGE_HTML}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can view your ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_new_staff_sub'] = "Ticket ID #{TICKET_ID}";

$lang['ticket_new_staff'] = <<<EOF
A new ticket has been created.

---------------------------

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
User: {UNAME}
Department: {DEPARTMENT}
Priority: {PRIORITY}

---------------------------

{MESSAGE}

---------------------------

You can manage this ticket using this link: {LINK}
EOF;

$lang['ticket_new_staff_html'] = <<<EOF
&lt;p&gt;A new ticket has been created.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt;Subject: {SUBJECT}&lt;br /&gt;User: {UNAME}&lt;br /&gt;Department: {DEPARTMENT}&lt;br /&gt;Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;{MESSAGE_HTML}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can manage this ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_new_guest_sub'] = "Ticket ID #{TICKET_ID}";

$lang['ticket_new_guest'] = <<<EOF
You have submitted a new guest ticket.  Our staff will review your ticket shortly and reply accordingly.

---------------------------

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
Department: {DEPARTMENT}
Priority: {PRIORITY}

Ticket Key: {KEY}

---------------------------

You can view your ticket using this link: {LINK}
EOF;

$lang['ticket_new_guest_html'] = <<<EOF
&lt;p&gt;You have submitted a new guest ticket.&amp;nbsp; Our staff will review your ticket shortly and reply accordingly.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt;Subject: {SUBJECT}&lt;br /&gt;Department: {DEPARTMENT}&lt;br /&gt;Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;Ticket Key: {KEY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can view your ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_new_guest_behalf_sub'] = "Ticket ID #{TICKET_ID}";

$lang['ticket_new_guest_behalf'] = <<<EOF
A new guest ticket has been submitted in your behalf.  Our staff will review your ticket shortly and reply accordingly.

---------------------------

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
Department: {DEPARTMENT}
Priority: {PRIORITY}

Ticket Key: {KEY}

---------------------------

{MESSAGE}

---------------------------

You can view your ticket using this link: {LINK}
EOF;

$lang['ticket_new_guest_behalf_html'] = <<<EOF
&lt;p&gt;A new guest ticket has been submitted in your behalf.&amp;nbsp; Our staff will review your ticket shortly and reply accordingly.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt;Subject: {SUBJECT}&lt;br /&gt;Department: {DEPARTMENT}&lt;br /&gt;Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;Ticket Key: {KEY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;{MESSAGE_HTML}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can view your ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_new_guest_staff_sub'] = "Ticket ID #{TICKET_ID}";

$lang['ticket_new_guest_staff'] = <<<EOF
A new guest ticket has been created.

---------------------------

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
User: {UNAME}
Department: {DEPARTMENT}
Priority: {PRIORITY}

---------------------------

{MESSAGE}

---------------------------

You can manage this ticket using this link: {LINK}
EOF;

$lang['ticket_new_guest_staff_html'] = <<<EOF
&lt;p&gt;A new guest ticket has been created.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt;Subject: {SUBJECT}&lt;br /&gt;User: {UNAME}&lt;br /&gt;Department: {DEPARTMENT}&lt;br /&gt;Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;{MESSAGE}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can manage this ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_reply_sub'] = "Ticket ID #{TICKET_ID} Reply";

$lang['ticket_reply'] = <<<EOF
A reply has been made to your ticket.

---------------------------

{REPLY}

---------------------------

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
Department: {DEPARTMENT}
Priority: {PRIORITY}

---------------------------

You can view your ticket using this link: {LINK}
EOF;

$lang['ticket_reply_html'] = <<<EOF
&lt;p&gt;A reply has been made to your ticket&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;{REPLY_HTML}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt;Subject: {SUBJECT}&lt;br /&gt;Department: {DEPARTMENT}&lt;br /&gt;Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can view your ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_reply_staff_sub'] = "Ticket ID #{TICKET_ID} Reply";

$lang['ticket_reply_staff'] = <<<EOF
A reply has been made to a ticket.

---------------------------

{REPLY}

---------------------------

Reply By: {ACTION_USER}

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
User: {UNAME}
Department: {DEPARTMENT}
Priority: {PRIORITY}

---------------------------

You can manage this ticket using this link: {LINK}
EOF;

$lang['ticket_reply_staff_html'] = <<<EOF
&lt;p&gt;A reply has been made to a ticket.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;{REPLY_HTML}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Reply By: {ACTION_USER}&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt;Subject: {SUBJECT}&lt;br /&gt;User: {UNAME}&lt;br /&gt;Department: {DEPARTMENT}&lt;br /&gt;Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can manage this ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_assign_staff_sub'] = "Ticket ID #{TICKET_ID} Assigned";

$lang['ticket_assign_staff'] = <<<EOF
You have been assigned a ticket.

---------------------------

Assigned By: {ACTION_USER}

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
User: {UNAME}
Department: {DEPARTMENT}
Priority: {PRIORITY}

---------------------------

{MESSAGE}

---------------------------

You can manage this ticket using this link: {LINK}
EOF;

$lang['ticket_assign_staff_html'] = <<<EOF
&lt;p&gt;You have been assigned a ticket.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Assigned By: {ACTION_USER}&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt; Subject: {SUBJECT}&lt;br /&gt; User: {UNAME}&lt;br /&gt; Department: {DEPARTMENT}&lt;br /&gt; Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;{MESSAGE_HTML}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can manage this ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_escalate_sub'] = "Ticket ID #{TICKET_ID} Escalated";

$lang['ticket_escalate'] = <<<EOF
Your ticket has been escalated.  Our managers will be reviewing your ticket shortly.

---------------------------

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
Department: {DEPARTMENT}
Priority: {PRIORITY}

---------------------------

You can view your ticket using this link: {LINK}
EOF;

$lang['ticket_escalate_html'] = <<<EOF
&lt;p&gt;Your ticket has been escalated.&amp;nbsp; Our managers will be reviewing your ticket shortly.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt;Subject: {SUBJECT}&lt;br /&gt;Department: {DEPARTMENT}&lt;br /&gt;Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can view your ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_escalate_staff_sub'] = "Ticket ID #{TICKET_ID} Escalated";

$lang['ticket_escalate_staff'] = <<<EOF
A ticket has been escalated.

---------------------------

Escalated By: {ACTION_USER}

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
User: {UNAME}
Department: {DEPARTMENT}
Priority: {PRIORITY}

---------------------------

{MESSAGE}

---------------------------

You can manage this ticket using this link: {LINK}
EOF;

$lang['ticket_escalate_staff_html'] = <<<EOF
&lt;p&gt;A ticket has been escalated.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Escalated By: {ACTION_USER}&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt; Subject: {SUBJECT}&lt;br /&gt; User: {UNAME}&lt;br /&gt; Department: {DEPARTMENT}&lt;br /&gt; Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;{MESSAGE}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can manage this ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_hold_sub'] = "Ticket ID #{TICKET_ID} On Hold";

$lang['ticket_hold'] = <<<EOF
Your ticket has been put on hold.

---------------------------

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
Department: {DEPARTMENT}
Priority: {PRIORITY}

---------------------------

You can view your ticket using this link: {LINK}
EOF;

$lang['ticket_hold_html'] = <<<EOF
&lt;p&gt;Your ticket has been put on hold.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt;Subject: {SUBJECT}&lt;br /&gt;Department: {DEPARTMENT}&lt;br /&gt;Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can view your ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_hold_staff_sub'] = "Ticket ID #{TICKET_ID} On Hold";

$lang['ticket_hold_staff'] = <<<EOF
A ticket has been put on hold.

---------------------------

Put on Hold By: {ACTION_USER}

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
User: {UNAME}
Department: {DEPARTMENT}
Priority: {PRIORITY}

---------------------------

You can manage this ticket using this link: {LINK}
EOF;

$lang['ticket_hold_staff_html'] = <<<EOF
&lt;p&gt;A ticket has been put on hold.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Put on Hold By: {ACTION_USER}&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt; Subject: {SUBJECT}&lt;br /&gt; User: {UNAME}&lt;br /&gt; Department: {DEPARTMENT}&lt;br /&gt; Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can manage this ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_move_sub'] = "Ticket ID #{TICKET_ID} Moved";

$lang['ticket_move'] = <<<EOF
Your ticket has been moved to the department: {NEW_DEPART}

---------------------------

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
Old Department: {OLD_DEPART}
New Department: {NEW_DEPART}
Priority: {PRIORITY}

---------------------------

You can view your ticket using this link: {LINK}
EOF;

$lang['ticket_move_html'] = <<<EOF
&lt;p&gt;Your ticket has been moved to the department: {NEW_DEPART}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt; Subject: {SUBJECT}&lt;br /&gt; Old Department: {OLD_DEPART}&lt;br /&gt;New Department: {NEW_DEPART}&lt;br /&gt; Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can view your ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_move_to_staff_sub'] = "Ticket ID #{TICKET_ID} Moved";

$lang['ticket_move_to_staff'] = <<<EOF
A ticket has been moved to your department.

---------------------------

Moved By: {ACTION_USER}

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
User: {UNAME}
Old Department: {OLD_DEPART}
New Department: {NEW_DEPART}
Priority: {PRIORITY}

---------------------------

{MESSAGE}

---------------------------

You can manage this ticket using this link: {LINK}
EOF;

$lang['ticket_move_to_staff_html'] = <<<EOF
&lt;p&gt;A ticket has been moved to your department.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Moved By: {ACTION_USER}&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt; Subject: {SUBJECT}&lt;br /&gt; User: {UNAME}&lt;br /&gt;Old Department: {OLD_DEPART}&lt;br /&gt;New Department: {NEW_DEPART}&lt;br /&gt; Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;{MESSAGE_HTML}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can manage this ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_move_away_staff_sub'] = "Ticket ID #{TICKET_ID} Moved";

$lang['ticket_move_away_staff'] = <<<EOF
A ticket has been moved from your department.

---------------------------

Moved By: {ACTION_USER}

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
User: {UNAME}
Old Department: {OLD_DEPART}
New Department: {NEW_DEPART}
Priority: {PRIORITY}

---------------------------

{MESSAGE}

---------------------------

You can manage this ticket using this link: {LINK}
EOF;

$lang['ticket_move_away_staff_html'] = <<<EOF
&lt;p&gt;A ticket has been moved from your department.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Moved By: {ACTION_USER}&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt; Subject: {SUBJECT}&lt;br /&gt; User: {UNAME}&lt;br /&gt;Old Department: {OLD_DEPART}&lt;br /&gt;New Department: {NEW_DEPART}&lt;br /&gt; Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;{MESSAGE_HTML}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can manage this ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_close_sub'] = "Ticket ID #{TICKET_ID} Closed";

$lang['ticket_close'] = <<<EOF
Your ticket has been closed.



---------------------------



Ticket ID: {TICKET_ID}

Subject: {SUBJECT}

Department: {DEPARTMENT}

Priority: {PRIORITY}



---------------------------



You can view your ticket using this link: {LINK}



If there is anything else we can do, please submit a new ticket.
EOF;

$lang['ticket_close_html'] = <<<EOF
&lt;p&gt;Your ticket has been closed.&lt;/p&gt;

&lt;p&gt;---------------------------&lt;/p&gt;

&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt;Subject: {SUBJECT}&lt;br /&gt;Department: {DEPARTMENT}&lt;br /&gt;Priority: {PRIORITY}&lt;/p&gt;

&lt;p&gt;---------------------------&lt;/p&gt;

&lt;p&gt;You can view your ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;

&lt;p&gt;If there is anything else we can do, please submit a new ticket.&lt;/p&gt;
EOF;

$lang['ticket_close_staff_sub'] = "Ticket ID #{TICKET_ID} Closed";

$lang['ticket_close_staff'] = <<<EOF
A ticket has been closed.

---------------------------

Closed By: {ACTION_USER}

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
User: {UNAME}
Department: {DEPARTMENT}
Priority: {PRIORITY}

---------------------------

You can manage this ticket using this link: {LINK}
EOF;

$lang['ticket_close_staff_html'] = <<<EOF
&lt;p&gt;A ticket has been closed.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Closed By: {ACTION_USER}&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt; Subject: {SUBJECT}&lt;br /&gt; User: {UNAME}&lt;br /&gt; Department: {DEPARTMENT}&lt;br /&gt; Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can manage this ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_reopen_sub'] = "Ticket ID #{TICKET_ID} Reopened";

$lang['ticket_reopen'] = <<<EOF
Your ticket has been reopened.  Our staff will review your ticket shortly and reply accordingly.

---------------------------

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
Department: {DEPARTMENT}
Priority: {PRIORITY}

---------------------------

You can view your ticket using this link: {LINK}
EOF;

$lang['ticket_reopen_html'] = <<<EOF
&lt;p&gt;Your ticket has been reopened.&amp;nbsp; Our staff will review your ticket shortly and reply accordingly.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt;Subject: {SUBJECT}&lt;br /&gt;Department: {DEPARTMENT}&lt;br /&gt;Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can view your ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_reopen_staff_sub'] = "Ticket ID #{TICKET_ID} Reopened";

$lang['ticket_reopen_staff'] = <<<EOF
A ticket has been reopened.

---------------------------

Reopened By: {ACTION_USER}

Ticket ID: {TICKET_ID}
Subject: {SUBJECT}
User: {UNAME}
Department: {DEPARTMENT}
Priority: {PRIORITY}

---------------------------

{MESSAGE}

---------------------------

You can manage this ticket using this link: {LINK}
EOF;

$lang['ticket_reopen_staff_html'] = <<<EOF
&lt;p&gt;A ticket has been reopened.&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;Reopened By: {ACTION_USER}&lt;/p&gt;
&lt;p&gt;Ticket ID: {TICKET_ID}&lt;br /&gt; Subject: {SUBJECT}&lt;br /&gt;User: {UNAME}&lt;br /&gt; Department: {DEPARTMENT}&lt;br /&gt; Priority: {PRIORITY}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;{MESSAGE_HTML}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You can manage this ticket using this link: &lt;a href=&quot;{LINK}&quot;&gt;{LINK}&lt;/a&gt;&lt;/p&gt;
EOF;

$lang['ticket_new_rejected_sub'] = "New Ticket Failed";

$lang['ticket_new_rejected'] = <<<EOF
We were unable to accept your email and create a ticket because you do not have permission to create tickets in this department.

If you feel this message is an error, please contact an administrator.
EOF;

$lang['ticket_new_rejected_html'] = <<<EOF
&lt;p&gt;We were unable to accept your email and create a ticket because you do not have permission to create tickets in this department.&lt;/p&gt;
&lt;p&gt;If you feel this message is an error, please contact an administrator.&lt;/p&gt;
EOF;

$lang['ticket_reply_rejected_sub'] = "Reply Failed";

$lang['ticket_reply_rejected'] = <<<EOF
We were unable to accept your email and create a reply because you do not have permission to reply to this ticket.

If you feel this message is an error, please contact an administrator.
EOF;

$lang['ticket_reply_rejected_html'] = <<<EOF
&lt;p&gt;We were unable to accept your email and create a reply because you do not have permission to reply to this ticket.&lt;/p&gt;
&lt;p&gt;If you feel this message is an error, please contact an administrator.&lt;/p&gt;
EOF;

$lang['news_sub'] = "News: {TITLE}";

$lang['news'] = <<<EOF
News Item: {TITLE}

---------------------------

{CONTENT}

---------------------------

You have received this email because you selected to receive email notifications for news items in your profile.  If you would like to discontinue these emails, log in and update your preferences.
EOF;

$lang['news_html'] = <<<EOF
&lt;p&gt;News Item: {TITLE}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;{CONTENT}&lt;/p&gt;
&lt;p&gt;---------------------------&lt;/p&gt;
&lt;p&gt;You have received this email because you selected to receive email notifications for news items in your profile.&amp;nbsp; If you would like to discontinue these emails, log in and update your preferences.&lt;/p&gt;
EOF;

$lang['test_sub'] = "Test Email";

$lang['test'] = <<<EOF
This is a test message from Trellis Desk.  If you have received this message, it is likely that your outgoing emails from Trellis Desk are working.
EOF;

$lang['test_html'] = <<<EOF
&lt;p&gt;This is a &lt;strong&gt;test&lt;/strong&gt; message from Trellis Desk.  If you have received this message, it is likely that your outgoing emails from Trellis Desk are working.&lt;/p&gt;
EOF;

?>