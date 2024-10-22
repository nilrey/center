from smtplib import SMTP
import datetime
import sys

debuglevel = 0

smtp = SMTP()
smtp.set_debuglevel(debuglevel)
smtp.connect('centr26.local.citis', 25)
smtp.login('lu1', '12345678')

from_addr = "lu1 <lu1@local.citis>"
to_addr = "lu2@local.citis"


arg1 = str(sys.argv[1])
# txt = sys.argv[2]


subj = "Test message "
date = datetime.datetime.now().strftime( "%d/%m/%Y %H:%M" )

message_text = "Hello\nThis is a mail from your server\n\nBye\n"

msg = "From: %s\nTo: %s\nSubject: %s\nDate: %s\n\n%s" % ( from_addr, to_addr, subj, date, message_text )

smtp.sendmail(from_addr, to_addr, msg)
smtp.quit()

print(arg1)
print('ok')
