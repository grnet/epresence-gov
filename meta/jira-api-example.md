def create_issue(jira_issue):
jira = JIRA(
options=settings.JIRA_OPTIONS, basic_auth=settings.JIRA_LOGIN)
user_fullname = jira_issue.user.first_name.el + " " + \
jira_issue.user.last_name.el
issue = {
'project': settings.JIRA_PROJECT,
'labels': [settings.JIRA_LABEL],
'summary': jira_issue.title,
'description': jira_issue.description,
'issuetype': {'name': ISSUE_TYPES.get(jira_issue.issue_type)},
'customfield_12350': user_fullname,
'customfield_12550': jira_issue.user.email,
'customfield_12553': jira_issue.user.mobile_phone_number
}
if jira_issue.reporter.is_helpdesk():
issue.update({'customfield_12751': jira_issue.reporter.username})

created_issue = jira.create_issue(issue)
logger.info("created jira issue %s" % created_issue.key)

return created_issue 

return created_issue 
ISSUE_TYPES = {
"complaint": "Παράπονα",
"error": "Πρόβλημα",
"general_information": "Γενικές πληροφορίες",
"account_modification": "Μεταβολή στοιχείων",
"registration": "Εγγρ
return created_issue 
ISSUE_TYPES = {
"complaint": "Παράπονα",
"error": "Πρόβλημα",
"general_information": "Γενικές πληροφορίες",
"account_modification": "Μεταβολή στοιχείων",
"registration": "Εγγραφή/Πιστοποίηση",
"login": "Θέματα πρόσβασης"
} 
