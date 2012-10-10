freepbx-csreporting
===================

FreePBX Custom Service Reporting

This customer service reporting module emails call volume statistics and copies of random phone calls (weekly) to designated address. Compiles weekly reports on call volume for the previous week.

This module allows the user to specify specific extensions that they want to monitor, as well as the extensions and email addresses of supervisors that will get identical information.

Weekly, emails containing call statistics such as average call time, number of inbound and outbound calls, etc. will be emailed to the email address on file for the specified extensions as well as the email address of the supervisors. Additionally, if recordings are enabled on the system and the user specified criteria are met, copies of random recordings will be delivered to the voicemail of the user for which the recording is for, as well as the voicemail of the specified supervisor's extensions.