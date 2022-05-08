from bs4 import BeautifulSoup
import mariadb
import argparse

# Parse arguments
parser = argparse.ArgumentParser(description='Process some integers.')
parser.add_argument('file', type=str,
    help='HTML file to parse')
parser.add_argument('uri', type=str, nargs='?',
    help='full URI of the web page')
args = parser.parse_args()
if args.uri == None:
    args.uri = "/"+args.file

# Connect to DB
db = mariadb.connect(
        user="root",
        password="azerty123",
        host="localhost",
        port=3306,
        database="test"
)
db.autocommit = False
cursor = db.cursor()

# Remove old data from the page
cursor.execute("DELETE FROM pages WHERE uri=?", (args.uri,))

# Parse HTML
html = open(args.file).read()
parsed = BeautifulSoup(html, features="lxml")

# Init tree

cursor.execute("INSERT INTO pages (uri) VALUES (?)", (args.uri,))
pageID = cursor.lastrowid

toParse = [(node, None) for node in parsed.children]

# Put into DB

while len(toParse) > 0:
    node, parentNodeID = toParse.pop(0)

    # Parse current node

    cursor.execute("INSERT INTO nodes (pageID, tagName, tagValue, parentNodeID) VALUES (?, ?, ?, ?)",
        (pageID, node.name, node.string, parentNodeID))
    nodeID = cursor.lastrowid

    # Les attributs
    for k, v in node.attrs.items():
        
        for attrV in (v if type(v) == list else (v,)):
            cursor.execute("INSERT INTO attrs (nodeID, attrName, attrValue) VALUES (?, ?, ?)",
                (nodeID, k, attrV))

    
    # Prepare for next nodes
    for childNode in reversed(node.find_all(recursive=False)):
        toParse.insert(0, (childNode, nodeID))


db.commit()
