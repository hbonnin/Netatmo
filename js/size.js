// <!-- <![CDATA[
function writeSize()
    {var x = $(document).width();
    var y = $(document).height();
    t ='width='+x+'&height='+y;
    return t;
    }
 function widthChart()
    {var x = $(document).width();
	var modules = document.getElementById("modules");
    var larg = 2*modules.offsetWidth + 14;
    lar = x - larg;
    return lar; 
    }  
function heightChart()
    {var y = $(document).height();
    var yy = 0;
    if(!isMobile())yy = 64;
    //yy = 64;
    var xx = $(window).width();
    if(xx < $(window).height())//Portrait
    	y = Math.min(y,1.*xx);
    else
    	y = Math.min(y,1.2*xx);
    var h = Math.round((y - yy)/2);
    return h;
    }      
function isMobile()
	{//var iPhone = /iPhone/i.test(navigator.userAgent);
    //var iPhone4 = (iPhone && pixelRatio == 2);
    //var iPhone5 = /iPhone OS 5_0/i.test(navigator.userAgent); // ?
    //var iPad = /iPad/i.test(navigator.userAgent);
    //var android = /android/i.test(navigator.userAgent);
    //var webos = /hpwos/i.test(navigator.userAgent);
    //var iOS = iPhone || iPad;
    var iOS =  navigator.userAgent.match(/iPhone|iPad|iPod/i);
    var Android =  navigator.userAgent.match(/Android/i);
    var mobile = iOS || Android ;
	return mobile;
	}
	
// ]]> -->
