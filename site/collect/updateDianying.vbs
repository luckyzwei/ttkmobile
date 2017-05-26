Dim IE
Set IE=CreateObject("InternetExplorer.Application")

IE.navigate("http://localhost:806/?s=update/dianying")
IE.visible=1

Set IE=Nothing