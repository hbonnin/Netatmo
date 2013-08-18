    function getXMLHttp()
        {var xmlHttp
        try
            {xmlHttp = new XMLHttpRequest();//Firefox, Opera 8.0+, Safari
            }
        catch(e)
            {try //Internet Explorer
                {xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
                }
            catch(e)
                {try
                    {xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                catch(e)
                    {alert("Your browser does not support AJAX!")
                    return false;
                    }
                }
            }
          return xmlHttp;
        }
    function MakeRequestLogin()
        {var xmlHttp = getXMLHttp();
        xmlHttp.onreadystatechange = function()
            {if(xmlHttp.readyState == 4)
                {HandleResponseLogin(xmlHttp.responseText);
                }
            }
        xmlHttp.open("GET","indexLogin.php", true); 
        xmlHttp.send(null);
        }	
    function HandleResponseLogin(response)
        {popup = window.open('indexLogin.php','Login','titlebar=yzs,menubar=0,status=0,scrollbars=0,location=0,toolbar=0,height=150,width=400');
        }	

    function loadLogin()
        {alert('loadLogin');
        //$(document).ready(function() {
            $.ajax(
                {type : 'POST', 
                url : 'login.php',
                data : { username: "John", password: "1234" },
                success : function(data)
                    {
                    }
                }
            );
            //});
        }
