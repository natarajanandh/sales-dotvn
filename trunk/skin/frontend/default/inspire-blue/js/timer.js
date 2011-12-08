/*
Author: Robert Hashemian
http://www.hashemian.com/

Modified for Client by: AA910

You can use this code in any manner so long as the author's
name, Web address and this disclaimer is kept intact.
********************************************************
Usage Sample:

<script language="JavaScript">
TargetDate = "12/31/2020 5:00 AM UTC-0500";
BackColor = "transparent";
ForeColor = "default";
CountActive = true;
CountStepper = -1;
LeadingZero = true;
DisplayFormat = "<span class='hour'>%%H%% </span><span class='min'>%%M%%</span><span class='sec'>%%S%%</span>";
FinishMessage = "";
</script>
<script language="JavaScript" src="http://www.coupme.com/v/vspfiles/templates/anise/countdown.js"></script>

*/

function calcage(secs, num1, num2) {
   if(num1==3600)
        {
            s = ((Math.floor(secs/num1))).toString();
        }
    else
        {
            s = ((Math.floor(secs/num1)%num2)).toString();
        }
    if (LeadingZero && s.length < 2)
        s = "0" + s;
    return "" + s + "";
}

function CountBack(secs) {
  if (secs < 0) {
    document.getElementById("cntdwn").innerHTML = FinishMessage;
    document.getElementById('deal_status').innerHTML ='<img src="'+dealimage+'"  alt="Rate" title="Rate"/><p>Deal closed !</p> ';
     document.getElementById('scriptbuynow').style.display = "none";
     document.getElementById('script_gift_box').style.display = "none";
     document.getElementById('nodeal').innerHTML ='<img src="'+buynowreplace+'" alt="Not Available" title="notavailable"/>';
    return;
  }
  DisplayStr = DisplayFormat.replace(/%%D%%/g, calcage(secs,86400,100000));
  DisplayStr = DisplayStr.replace(/%%H%%/g, calcage(secs,3600,96)); /* was 3600, 48*/
  DisplayStr = DisplayStr.replace(/%%M%%/g, calcage(secs,60,60));
  DisplayStr = DisplayStr.replace(/%%S%%/g, calcage(secs,1,60));

  document.getElementById("cntdwn").innerHTML = DisplayStr;
  if (CountActive)
    setTimeout("CountBack(" + (secs+CountStepper) + ")", SetTimeOutPeriod);
}

function putspan(backcolor, forecolor) {
 document.write("<span id='cntdwn' style='background-color:" + backcolor +
                "; color:" + forecolor + "'></span>");
}

if (typeof(BackColor)=="undefined")
  BackColor = "transparent";
if (typeof(ForeColor)=="undefined")
  ForeColor= "";
if (typeof(TargetDate)=="undefined")
  TargetDate = targetdate;
  CurrentDate = startdate;   /* IMPORTANT: In daylight savings time (NOV-MAR), ALWAYS set your expiration time as one hour ahead. So if it expires at 5pm real time, set it to 6pm. */
if (typeof(DisplayFormat)=="undefined")
  DisplayFormat = "<span class='hour' style='margin:2px 0 0 0'>%%H%%</span><span class='min'  style='margin:2px 0 0 78px'>%%M%%</span><span class='sec' style='margin:2px 0 0 149px'>%%S%%</span>";
if (typeof(CountActive)=="undefined")
  CountActive = true;
if (typeof(FinishMessage)=="undefined"){
  FinishMessage = "<span class='hour' style='margin:2px 0 0 0'>00</span><span class='min' style='margin:2px 0 0 65px'>00</span><span class='sec' style='margin:2px 0 0 135px'>00</span>";
  }
if (typeof(CountStepper)!="number")
  CountStepper = -1;
if (typeof(LeadingZero)=="undefined")
  LeadingZero = true;


CountStepper = Math.ceil(CountStepper);
if (CountStepper == 0)
  CountActive = false;
var SetTimeOutPeriod = (Math.abs(CountStepper)-1)*1000 + 990;
putspan(BackColor, ForeColor);
var dthen = new Date(TargetDate);
var dnow = new Date(CurrentDate);
if(CountStepper>0)
  ddiff = new Date(dnow-dthen);
else
  ddiff = new Date(dthen-dnow);
gsecs = Math.floor(ddiff.valueOf()/1000);
CountBack(gsecs);