import MySQLdb,sys,re
sys.path.append('/home/deltaquad/pywikipedia/core')
import pywikibot as wikipedia
import credentials

def database_connect(type):
	if type == "open":
		querycontent="""status != "closed" AND status != "unverified" AND status != "awaiting_user";"""
		tableheader = """;Appeals needing attention\n{| align="center" class="wikitable sortable" style="align: center; float:center; font-size: 90%; text-align:center" cellspacing="0" cellpadding="1" valign="middle" """
	elif type == "wait":
		querycontent="""status = "awaiting_user";"""
		tableheader = """;Appeals awaiting response from blocked user\n{| align="center" class="wikitable collapsible sortable" style="align: center; float:center; font-size: 90%; text-align:center" cellspacing="0" cellpadding="1" valign="middle" """
	db = MySQLdb.connect(host="localhost",user=credentials.mysqluser,passwd=credentials.mysqlpass,db="utrs")
	cursor = db.cursor()
	print "Query content: " + querycontent
	cursor.execute("SELECT appealID, hasAccount, blockingAdmin, ip, wikiAccountName, timestamp, status FROM utrs.appeal WHERE "+querycontent)
	numrows = int(cursor.rowcount)
	database = cursor.fetchall()
	return formatDBoutput(database,tableheader)

def formatDBoutput(database,tableheader):
	alltext=""
	for row in database:
		print row
		finalString = "\n|-\n|[https://utrs.wmflabs.org/appeal.php?id="+str(row[0])+" "+str(row[0])+"]\n|"
		if row[1] == 1:#hasAccount
			finalString = finalString + "{{user3|" + str(row[4]) + "}}\n|"
		else:
			finalString = finalString + "{{user3|" + str(row[3]) + "}}\n|"
		finalString = finalString + str(row[5]) + "\n|" + str(row[6])
		alltext = alltext + finalString
		finalString = ""
	return generateTable(tableheader,alltext)
def generateTable(header,alltext):
	sectionheads="""|-
	!Appeal Number
	!Appealant
	!Appeal Filled
	!Status"""
	endtable="\n|}"
	table = header+sectionheads+alltext+endtable+"\n"
	return table
def callDB(type):
	return database_connect(type)
site = wikipedia.getSite()
pagename = "User:DeltaQuad/UTRS Appeals"
page = wikipedia.Page(site, pagename)
open=callDB("open")
wait=callDB("wait")
page.put(open+wait, comment="Updating UTRS caselist")

