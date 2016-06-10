import re
import time
codef=""
templatef=open("C:\\Users\\DeltaQuad\\Desktop\\translatefile.txt","r")
typefile=input("\nWhat Type of file is this?\n1-Web Viewable\n2-Source\n")
filename=raw_input("\nPlease enter the filename of the code: ")
if typefile == 1:
    codefn="C:\\Users\\DeltaQuad\\Documents\\GitHub\\utrs\\public_html\\"+filename+".php"
if typefile == 2:
    codefn="C:\\Users\\DeltaQuad\\Documents\\GitHub\\utrs\\public_html\\src\\"+filename+".php"
template = templatef.read()
templatenew=template.split("\n")
for line in templatenew:
    search=""
    print "Text: " + line.split(" > ")[0]
    msgname=raw_input("\nPlease Enter a Message name: ")
    msgtype=input("What is the message type:\n1-System\n2-Log\n3-Error\n4-Informational\n5-Links\n")
    search="\)\;\n\tpublic static \$"
    if msgtype == 1:
        stype="log"
        rtype="system"
    if msgtype == 2:
        stype="error"
        rtype="log"
    if msgtype == 3:
        stype="links"
        rtype="error"
    if msgtype == 4:
        stype="system"
        rtype="information"
    if msgtype == 5:
        stype="tos"
        rtype="links"
    search+=stype
    newline = line.replace("\"","").split(" > ")[0]
    add = ",\n"+ "\t\t\"" + msgname +"\" => array (\n"+"\t\t\t\"en\" => \""+newline+"\",\n"+"\t\t\t\"pt\" => \"{pt "+msgname+"}\"\n"+"\t\t)"
    msgf=open("C:\\Users\\DeltaQuad\\Documents\\GitHub\\utrs\\public_html\\src\\messages.php","r")
    msgtext = msgf.read()
    msgf.close()
    text = re.sub(search,add+");\n\tpublic static $"+stype,msgtext,1)
    time.sleep(2)
    msgf=open("C:\\Users\\DeltaQuad\\Documents\\GitHub\\utrs\\public_html\\src\\messages.php","w")
    msgf.write(text)
    msgf.close()
    codef=open(codefn,"r")
    code = codef.read()
    codef.close()
    msgtext=""
    if " > " in line:
        text = code.replace(line.split(" > ")[1],line.split(" > ")[2].replace("[$msg]","['"+msgtext+"']"))
    else:
        text = code.replace(line,"SystemMessages::$"+rtype+"['"+msgname+"'][$lang]",20)
    codef=open(codefn,"w")
    codef.write(text)
    codef.close()
templatef.close()
