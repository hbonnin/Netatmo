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
function height()
    {var w = window,
    d = document,
    e = d.documentElement,
    g = d.getElementsByTagName('body')[0],
    y = w.innerHeight|| e.clientHeight|| g.clientHeight;
    var h = Math.round((y - 70)/2);
    h = Math.max(h,290);
    return h;
    }    
// ]]> -->
