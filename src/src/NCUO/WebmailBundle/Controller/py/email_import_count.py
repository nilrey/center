import imaplib, email
from email.header import decode_header
import json
import sys
import os
from urllib.parse import unquote

login = str(sys.argv[1])
pwd = str(sys.argv[2])
server_id = str(sys.argv[3])

imap = imaplib.IMAP4(server_id, 143)         # establish connection
# imap.login("lu1", "12345678")  # login
imap.login(login, pwd)  # authorization
# print(imap.list())                       # print various inboxes
status, messages = imap.select("INBOX")  # select inbox

# status, messages = imap.search(None, search_string )

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


numOfMessages = int(messages[0]) # get number of messages

print(numOfMessages)

imap.close()