import sys
import os
from smtplib import SMTP
from email.mime.multipart import MIMEMultipart, MIMEBase
from email.mime.text import MIMEText
from email.encoders import encode_base64
import json
from urllib.parse import unquote

login = str(sys.argv[1])
pwd = str(sys.argv[2])
from_email = str(sys.argv[3]) 
to_emails = [str(sys.argv[4])] # ['user1@server.com', 'user2@server.com']
subj = json.loads(sys.argv[5]) # subj
message_text = json.loads(sys.argv[6]) # message_text
message_hash = str(sys.argv[7]) # message_text

fname1UrlEnc = sys.argv[8]
fname1 = unquote(fname1UrlEnc)
# print(fname1);
# Connect, authenticate, and send mail
smtp_server = SMTP()
smtp_server.set_debuglevel(0)
smtp_server.connect('centr26.local.citis', 25)
# smtp_server.login("lu1", "12345678")  # login
smtp_server.login(login, pwd)  # login

# Create multipart MIME email
email_message = MIMEMultipart()
email_message.add_header('Message-Id', message_hash )
email_message.add_header('To', ', '.join(to_emails))
email_message.add_header('From', from_email)
email_message.add_header('Subject', subj)
email_message.add_header('X-Priority', '1')  # Urgent/High priority

# Create text and HTML bodies for email
text_part = MIMEText(message_text, 'plain')
html_part = MIMEText(message_text, 'html')

# Attach all the parts to the Multipart MIME email
email_message.attach(text_part)
email_message.attach(html_part)

if fname1UrlEnc != "":
	# Create file attachment
	attachment = MIMEBase("application", "octet-stream")
	# attachment.set_payload(b'\xDE\xAD\xBE\xEF\xDE\xAD\xBE\xEF\xDE\xAD\xBE\xEF\xDE\xAD\xBE\xEF')  # Raw attachment data. File content
	fileRoot = '/var/www/html/public/webmail/upload/'+fname1
	attachment.set_payload(open(fileRoot,"rb").read() )
	encode_base64(attachment)
	attachment.add_header("Content-Disposition", "attachment; filename="+fname1UrlEnc)

	email_message.attach(attachment)

# attachment1 = MIMEBase("application", "octet-stream")
# attachment1.set_payload(open('/var/www/html/public/webmail/test2.txt',"rb").read() )
# encode_base64(attachment1)
# attachment1.add_header("Content-Disposition", "attachment; filename=test2.txt")
# email_message.attach(attachment1)

smtp_server.sendmail(from_email, to_emails, email_message.as_bytes())

# Disconnect
smtp_server.quit()

print('ok')
