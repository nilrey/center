# Import smtplib for the actual sending function
import smtplib

# Import the email modules we'll need
from email.mime.text import MIMEText

server = 'smtp.ncuo-portal.local'
user = 'citis'
password = 'Ut2Fwy'
# Open a plain text file for reading.  For this example, assume that
# the text file contains only ASCII characters.
# with open(textfile, 'rb') as fp:
#     # Create a text/plain message
#     msg = MIMEText(fp.read())

# me == the sender's email address
# you == the recipient's email address
msg = MIMEText('test message')
msg['Subject'] = 'The contents of test'  
msg['From'] = 'citis@ncuo-portal.local'
msg['To'] = 'citis@ncuo-portal.local'

# Send the message via our own SMTP server, but don't include the
# envelope header.

# mail = smtplib.SMTP(server)


mail = smtplib.SMTP_SSL(server)
mail.login(user, password)

mail.sendmail(user, user, msg.as_string())
mail.quit()