Dim IE
Set IE=CreateObject("InternetExplorer.Application")

IE.navigate("http://localhost:804/index.php?c=task&a=index&debug=1")
IE.visible=1

Set IE=Nothing