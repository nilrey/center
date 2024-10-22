import imaplib, email
from email.header import decode_header
import json
import sys

imap = imaplib.IMAP4("centr26.local.citis", 143)         # establish connection
# imap.login("lu2", "12345678")  # login
imap.login(str(sys.argv[1]), str(sys.argv[2]))  # login
message_id = str(sys.argv[3])
if message_id[-1:] == '@':
    search_string = '(TEXT "Message-Id: <%s")' % (message_id)
else:
    search_string = '(TEXT "%s")' % (message_id)
# print(imap.list())  # print various inboxes
imap.select("INBOX")  # select inbox

status, messages = imap.search(None, search_string )
 
def clean(text):
    # clean text for creating a folder
    return "".join(c if c.isalnum() else "_" for c in text)
 
def obtain_header(msg):
    # decode the email subject
    subject, encoding = decode_header(msg["Subject"])[0]
    if isinstance(subject, bytes):
        subject = subject.decode(encoding)
 
    # decode email sender
    From, encoding = decode_header(msg.get("From"))[0]
    if isinstance(From, bytes):
        From = From.decode(encoding)

    # decode email sender
    deliveryDate, encoding = decode_header(msg.get("Delivery-date"))[0]
    if isinstance(deliveryDate, bytes):
        deliveryDate = deliveryDate.decode(encoding)
 
    messageId, encoding = decode_header(msg.get("Message-Id"))[0]
    if isinstance(messageId, bytes):
        messageId = messageId.decode(encoding)
        if messageId[0:1] == '<':
            messageId = messageId[1:-1]

    # print("Subject:", subject)
    # print("From:", From)
    return subject, From, deliveryDate, messageId
 
def download_attachment(part):
    # download attachment
    filename = part.get_filename()
 
    if filename:
        folder_name = clean(subject)
        if not os.path.isdir(folder_name):
            # make a folder for this email (named after the subject)
            os.mkdir(folder_name)
            filepath = os.path.join(folder_name, filename)
            # download attachment and save it
            open(filepath, "wb").write(part.get_payload(decode=True))

output = {}
if 'message_id' in locals() :
    if message_id[-1:] == '@': 
        search_string = '(TEXT "Message-Id: <%s")' % (message_id)
    else:
        search_string = '(TEXT "%s")' % (message_id)
    i = 0
    status, messages = imap.search(None, search_string )
    for num in messages[0].split():
        imap.store(num, '+FLAGS', '\\Deleted')
        # res, msg = imap.fetch(num, '(RFC822)')
        # for response in msg:
        #     if isinstance(response, tuple):
        #         msg = email.message_from_bytes(response[1])
        #         subject, From, deliveryDate, messageId = obtain_header(msg)
        #         body = msg.get_payload(decode=True).decode()
        #         out = {'status':'Ok', 'messageId':messageId, 'subj':subject, 'from':From, 'date':deliveryDate, 'content':body}
        # output[str(i)] = (json.dumps(out) )
        # i+=1
    imap.expunge()
    output = {'status' : 'Ok', 'text': '' }
    # imap.close()
    # imap.logout()        
else:
    output = {'status' : 'Error', 'text': 'Incorrect Id' }

print('['+json.dumps(output)+']')

imap.close()