// <!-- <![CDATA[
function valider(frm)
	{
	var date0 = frm.elements['date0'].value;
	var tab = frm.elements['select'];
	for (var i = 0;i < tab.length;i++)
		{if(tab[i].selected)
			{var inter = tab[i].value;
			break;
			}
		}
	var saisie = (date0).split('/');
	var date = new Date(eval(saisie[2]),eval(saisie[1])-1,eval(saisie[0]));
	var date1 = frm.elements['date1'].value;
	var saisie1 = (date1).split('/');
	var endday = new Date(eval(saisie1[2]),eval(saisie1[1])-1,eval(saisie1[0]));
	if((endday - date < 24*60*60*1000) && (i < 2))	
		{frm.date1.focus();		
		alert('Date ' + date.getDate() +'/'+ (date.getMonth()+1) +'/'+ date.getFullYear()
		 +' non inférieure à '+ endday.getDate() +'/'+ (endday.getMonth()+1) +'/'+ endday.getFullYear() );
    	return false;
    	}
 	// i=0 1week i=1 1day  i=2 3hours i=3 30minute	i=4 max
	var nmesure = (endday-date)/(24*60*60*1000);
	if(i == 2)nmesure *= 8;
	else if(i == 3){nmesure *= 48;return true;}
	else if(i == 4){nmesure *= 288;return true;}
	else if(i == 0)nmesure /= 7;
	nmesure = Math.floor(nmesure+.5);		  	
    if(nmesure > 1024) 
    	{alert(nmesure + ' > max 1024 mesures');
    	return false;
    	}	
    return true;
  }
function Allow(tab) 
    {for (var i = 0;i < tab.length;i++)
		if(tab[i].selected)break;		
	var el1 = document.getElementById('id_date1');
	var el0 = document.getElementById('id_date0');
	var duree = document.getElementById('id_duree');
/*	
	var w = window,
    d = document,
    e = d.documentElement,
    g = d.getElementsByTagName('body')[0],
    x = w.innerWidth || e.clientWidth || g.clientWidth,
    y = w.innerHeight|| e.clientHeight|| g.clientHeight;
alert(x);
*/
    if(i < 3)
		{duree.innerHTML = ' ';
		el0.disabled = false;
		el0.hidden = false;
		el1.disabled=false;
		el1.hidden = false;
		}
	else if(i == 3)
		{duree.innerHTML = '(14 jours)';
		el0.disabled = true;
		el0.hidden = true;
		el1.disabled = false;
		el1.hidden = false;
		}				
	else
		{duree.innerHTML = '(2 jours)';
		el0.disabled=true;
		el0.hidden=true;
		el0.disabled = true;
		el1.disabled=true;
		el1.hidden = true;
		}	
    return true;		
	}
// ]]> -->