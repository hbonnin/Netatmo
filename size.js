// <!-- <![CDATA[
function writeSize()
    {var w = window,
    d = document,
    e = d.documentElement,
    g = d.getElementsByTagName('body')[0],
    x = w.innerWidth || e.clientWidth || g.clientWidth,
    y = w.innerHeight|| e.clientHeight|| g.clientHeight;
    t ='width='+x+'&height='+y;
    return t;
    }
function heightChart()
    {var w = window,
    d = document,
    e = d.documentElement,
    g = d.getElementsByTagName('body')[0],
    y = w.innerHeight|| e.clientHeight|| g.clientHeight;
    //var iPhone = /iPhone/i.test(navigator.userAgent);
    //if(iPhone)y = 544;    
    var h = Math.round((y - 70)/2);
    h = Math.max(h,290);
    return h;
    }   
 function widthChart()
    {var w = window,
    d = document,
    e = d.documentElement,
    g = d.getElementsByTagName('body')[0],
	x = w.innerWidth || e.clientWidth || g.clientWidth,
	lar = x - 190;
    return lar; 
    }  
function isMobile()
	{var iPhone = /iPhone/i.test(navigator.userAgent);
    var iPhone4 = (iPhone && pixelRatio == 2);
    var iPhone5 = /iPhone OS 5_0/i.test(navigator.userAgent); // ?
    var iPad = /iPad/i.test(navigator.userAgent);
    var android = /android/i.test(navigator.userAgent);
    var webos = /hpwos/i.test(navigator.userAgent);
    var iOS = iPhone || iPad;
    var mobile = iOS || android || webos;
	return mobile;
	}
function closewebapp()
	{document.write("<section id=\"sectionndjfrjvbjreiajjzkf\"><\/section>");
	document.write("<style>");
	document.write("#sectionndjfrjvbjreiajjzkf {");
	document.write("  height: -webkit-calc(0% + 0px);");
	document.write("  -webkit-transition: height 1s ease-in;");
	document.write("  z-index:-999;");
	document.write("}");
	document.write("<\/style>");
	}
	
// ]]> -->
