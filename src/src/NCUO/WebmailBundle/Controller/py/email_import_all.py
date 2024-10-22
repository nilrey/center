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

output = {}

i = 0
# for num in messages[0].split():
for num in range(numOfMessages, 0, -1):
    res, msg = imap.fetch(str(num), '(RFC822)')
    for response in msg:
        if isinstance(response, tuple):
            msg = email.message_from_bytes(response[1])
            subject, From, deliveryDate, messageId = obtain_header(msg)
            textPlain = 'textPlain'
            textHtml = 'textHtml'
            hasAttach = 'no'
            fileAttach = ''
            body =''
            filePath = '/var/www/html/public/webmail/attachments/'
            boundary = msg.get_boundary().replace('=', '')
            fileRoot = []
            fileNameList = []

            if msg.is_multipart():
                # iterate over email parts
                attachmentCnt = 0
                for part in msg.walk():
                    # if part.get_content_maintype() == 'multipart':
                    #     print(part.get_content_maintype() )
                    # print(part.get_content_maintype())
                    # extract content type of email
                    content_type = part.get_content_type()
                    content_disposition = str(part.get("Content-Disposition"))
 
                    try:
                        if part.get_content_maintype() == 'multipart':
                            pass
                        elif part.get_content_maintype() == 'text':
                            if part.get_content_subtype() == 'plain':
                                textPlain = part.get_payload(decode=True).decode()
                                # pass
                            elif part.get_content_subtype() == 'html':
                                textHtml = part.get_payload(decode=True).decode()
                                # pass
                        elif part.get_content_maintype() == 'application':
                            attachmentCnt +=1
                            hasAttach = 'yes'
                            fileContent = part.get_payload()
                            fileName = part.get_filename()

                            newpath = filePath+boundary+'/'+str(attachmentCnt)
                            if not os.path.exists(newpath):
                                os.makedirs(newpath)

                            with open(newpath+'/'+fileName, 'w') as file:
                                file.write(fileContent)

                            fileRoot.append(boundary+'/'+str(attachmentCnt)+'/'+fileName)
                            fileNameList.append(unquote(fileName))
                        # body = part.get_payload(decode=True).decode()
                    except:
                        pass
 
            else:
                # extract content type of email
                content_type = msg.get_content_type()
                # get the email body
                textPlain = msg.get_payload(decode=True).decode()

            # out = {'status':'Ok', 'messageId':messageId, 'subj':subject, 'from':From, 'date':deliveryDate, 'content':''}
            out = {
                'status':'Ok', 
                'messageId':messageId, 
                'subj':subject, 
                'from':From, 
                'date':deliveryDate, 
                'content':textHtml, # определить какой тип передавать. Выбор пользователя?
                'textplain':textPlain, 
                'texthtml':textHtml, 
                'hasattach':hasAttach,
                'fileRoot':fileRoot,
                'fileNameList':fileNameList
                }
    output[str(i)] = (json.dumps(out) )
    i+=1

print('['+json.dumps(output)+']')

imap.close()