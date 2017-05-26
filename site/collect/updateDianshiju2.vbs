Dim IE
Set IE=CreateObject("InternetExplorer.Application")

IE.navigate("http://localhost:806/?s=update/dianshiju/flag/1")
IE.visible=1

Set IE=Nothing