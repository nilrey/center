import imaplib, email
from email.header import decode_header
import json
import sys
import os
from urllib.parse import unquote
import base64

login = sys.argv[1]
pwd = sys.argv[2]
server_ip = sys.argv[3]

imap = imaplib.IMAP4(server_ip, 143)         # establish connection
imap.login(login, pwd)  # login
# message_id = str(sys.argv[3])

# print(imap.list())                       # print various inboxes
status, messages = imap.select("INBOX")  # select inbox
# # search_string = '(TEXT "Message-Id: %s")' % (message_id)
# if message_id[-1:] == '@':
#     search_string = '(TEXT "Message-Id: <%s")' % (message_id)
# else:
#     search_string = '(TEXT "%s")' % (message_id)

# status, messages = imap.search(None, search_string )

numOfMessages = int(messages[0]) # get number of messages
maildir = '/var/www/html/public/webmail/reserve/'+login

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

def saveTextFile(filePath, content):
        with open(filePath, 'w') as file:
            file.write(str(content))

output = {}
# i = 0
# recreate user directory
if( os.path.isdir(maildir) ):
    os.system("rm -rf "+maildir)
os.mkdir(maildir)

for i in range(numOfMessages, 0, -1):
    res, msg = imap.fetch(str(i), '(RFC822)')
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

                            fileDir = maildir+'/'+messageId
                            if( not os.path.isdir(fileDir) ):
                                os.mkdir(fileDir)
                            atachDir = fileDir+'/attach_base64'
                            # print(atachDir+'/'+fileName)
                            if( not os.path.isdir(atachDir) ):
                                os.mkdir(atachDir)

                            with open(atachDir+'/'+unquote(fileName), 'w') as file:
                                file.write(fileContent)

                            # fileRoot.append(atachDir+'/'+fileName)
                            # fileNameList.append(unquote(fileName))
                        # body = part.get_payload(decode=True).decode()
                    except:
                        pass
 
            else:
                # extract content type of email
                content_type = msg.get_content_type()
                # get the email body
                textPlain = msg.get_payload(decode=True).decode()

            out = {
                'status':'Ok', 
                'messageId':messageId, 
                'from':From, 
                'date':deliveryDate, 
                'hasattach':hasAttach,
                # 'subj':subject, 
                # 'content':textHtml, # определить какой тип передавать. Выбор пользователя?
                # 'textplain':textPlain, 
                # 'texthtml':textHtml, 
                # 'fileRoot':fileRoot,
                # 'fileNameList':fileNameList
                }
    output[str(i)] = (json.dumps(out) )
    # i+=1
    fileDir = maildir+'/'+messageId
    if( not os.path.isdir(fileDir) ):
        os.mkdir(fileDir)

    if( os.path.isdir(fileDir) ):
        filePath = fileDir +'/info.txt'
        saveTextFile(filePath,out)
    else:
        output[0] = {'status' : 'Error', 'text': 'Error save file: ' + filePath  }

print('['+json.dumps(output)+']')

imap.close()