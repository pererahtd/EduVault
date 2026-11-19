/* EduVault Dashboard: live statistics, weekly trend and existing report buttons.
   Scoped exclusively to Dashboard.php. No changes to original CSS or other pages. */
(function () {
  'use strict';
  const endpoint = 'dashboard_live_data.php';
  let latest = null;
  let busy = false;
  const $ = id => document.getElementById(id);
  const fmt = n => Number(n).toLocaleString();
  const esc = value => String(value == null ? '' : value).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const saved = (key) => { try { const x = JSON.parse(localStorage.getItem(key) || '[]'); return Array.isArray(x) ? x : []; } catch (_) { return []; } };
  const weekDay = date => new Date(date + 'T12:00:00').toLocaleDateString('en-GB', {weekday:'short'});
  function status(message, error) {
    let e = $('ev-live-report-status');
    if (!e) {
      e = document.createElement('div');
      e.id = 'ev-live-report-status'; e.setAttribute('role', 'status'); e.setAttribute('aria-live','polite');
      e.style.cssText = 'font-size:12px;margin-top:8px;min-height:16px;color:#9db5d5';
      const a = document.querySelector('.report-actions');
      if (a) a.insertAdjacentElement('afterend',e);
    }
    e.style.color = error ? '#ffba82' : '#9db5d5'; e.textContent = message;
  }
  function setChartMessage(message) {
    let e = $('ev-live-chart-note');
    if (!e) {
      e = document.createElement('p');e.id='ev-live-chart-note';
      e.style.cssText='font-size:12px;color:#a8bedf;margin:5px 0 0';
      document.querySelector('.report-chart-wrap')?.insertAdjacentElement('afterend',e);
    }
    e.textContent = message;
  }
  function renderStats(data) {
    Object.entries(data.stats || {}).forEach(([name,number]) => {
      const el = document.querySelector('[data-stat-value="'+name+'"]');
      if (el) { el.textContent = Number.isFinite(number) ? fmt(number) : 'N/A'; el.title = Number.isFinite(number) ? 'Live saved records' : 'Source unavailable'; }
    });
    const total = $('overview-total');
    if (total) total.textContent = Number.isFinite(data.stats?.materials) ? fmt(data.stats.materials) : 'N/A';
  }
  function renderChart(data) {
    const svg = document.querySelector('.report-chart-wrap svg');
    if (!svg) return;
    const values = (data.daily || []).map(n => Math.max(0,Number(n)||0));
    if (values.length !== 7) return;
    const maximum = Math.max(4,...values), top = Math.ceil(maximum / 4) * 4;
    const bottom = 245, height = 204, xs=[100,205,310,415,520,625,730];
    const yy = n => bottom - height * n / top;
    const line = svg.querySelector('.reference-trend-line');
    if (line) line.setAttribute('points',values.map((v,i)=>xs[i]+','+yy(v).toFixed(1)).join(' '));
    const points = svg.querySelectorAll('.chart-point');
    points.forEach((p,i) => {
      p.querySelectorAll('circle').forEach(c => c.setAttribute('cy',yy(values[i]).toFixed(1)));
      const t = p.querySelector('title');
      if (t) t.textContent = (data.days?.[i] || weekDay(data.days[i]))+': '+values[i]+' recorded activities';
    });
    const grid = svg.querySelectorAll('.chart-gridline');
    const ticks = Array.from(svg.querySelectorAll('.chart-axis-label')).filter(e=>e.getAttribute('text-anchor')==='end');
    for (let i=0;i<5;i++) {
      const v=top*i/4, y=yy(v);
      if (grid[i]) { grid[i].setAttribute('y1',y.toFixed(1)); grid[i].setAttribute('y2',y.toFixed(1)); }
      if (ticks[i]) { ticks[i].setAttribute('y',(y+4).toFixed(1)); ticks[i].textContent=String(Math.round(v)); }
    }
    const days = Array.from(svg.querySelectorAll('.chart-axis-label')).filter(e=>e.getAttribute('text-anchor')==='middle');
    data.days.forEach((d,i)=>{ if(days[i]){days[i].textContent=weekDay(d);days[i].querySelector('title')?.remove();} });
    svg.closest('.report-chart-wrap')?.setAttribute('aria-label','Activity trend for last 7 days, '+values.join(', ')+' records');
    const available = data.daily_sources || [];
    setChartMessage(available.length ? 'Live daily activity: '+available.join(', ')+'. Last updated '+new Date(data.generated_at).toLocaleTimeString() : 'Daily activity source unavailable.');
  }
  async function refresh(force) {
    if (busy && !force) return latest;
    busy = true;
    try {
      const response = await fetch(endpoint,{cache:'no-store',credentials:'same-origin',headers:{'Accept':'application/json','X-EV-Ajax':'1'}});
      if (!response.ok) throw new Error('Live data HTTP '+response.status);
      const content = await response.json();
      if (!content || !content.ok || !content.stats) throw new Error('Invalid live data response');
      latest=content;renderStats(content);renderChart(content);
      if(content.unavailable?.length) status('Some live data sources unavailable: '+content.unavailable.join(', '),true);
      else status('Live data updated '+new Date(content.generated_at).toLocaleTimeString(),false);
      return latest;
    } catch(e) {
      console.error('EduVault live report:',e);
      if (!latest) {
        ['users','materials','pending','forums'].forEach(n=>{const el=document.querySelector('[data-stat-value="'+n+'"]');if(el)el.textContent='N/A';});
        const total=$('overview-total');if(total)total.textContent='N/A';
        renderChart({daily:[0,0,0,0,0,0,0],days:Array.from({length:7},(_,i)=>{const d=new Date();d.setDate(d.getDate()-6+i);return [d.getFullYear(),String(d.getMonth()+1).padStart(2,'0'),String(d.getDate()).padStart(2,'0')].join('-');}),daily_sources:[]});
      }
      status('Live data is unavailable. Check dashboard_live_data.php and server logs.',true);
      return latest;
    } finally {busy=false;}
  }
  window.evRefreshLiveDashboard = () => refresh(false);
  // Keep the existing graph and cards but refresh source values from the server.
  refresh(false);
  setInterval(()=>{if (!document.hidden) refresh(false);},15000);
  document.addEventListener('visibilitychange',()=>{if(!document.hidden)refresh(false);});
  window.addEventListener('focus',()=>refresh(false));
  window.addEventListener('storage',()=>refresh(false));

  function reportData(live) {
    if (!live) throw new Error('Live data cannot be loaded. Report not generated.');
    const local = {
      events:saved('eduvault_admin_events_v1'),
      announcements:saved('eduvault_admin_announcements_v1'),
      notices:saved('eduvault_admin_notices_v1'),
      subjects:saved('eduvault_admin_subjects_v1'),
      batches:saved('eduvault_admin_batches_v1')
    };
    const sections = [
      ['Users', live.records.users || [],'Database'],
      ['Study Materials',live.records.materials || [],'Materials storage'],
      ['Discussion Forums & Q&A',live.records.forums || [],'Database'],
      ['Events',local.events,'Browser records'],
      ['Announcements',local.announcements,'Browser records'],
      ['Notices',local.notices,'Browser records'],
      ['Subjects',local.subjects,'Browser records'],
      ['Batches',local.batches,'Browser records']
    ];
    const stats={...live.stats};
    return {date:new Date(), generated:live.generated_at,stats,days:live.days,daily:live.daily,sections,unavailable:live.unavailable || []};
  }
  function rowsFor(records) {
    return records.map((r,i)=>{
      const obj = (r && typeof r==='object') ? r : {value:r};
      const safe = Object.fromEntries(Object.entries(obj).filter(([key,val])=>!['dataUrl','attachment','file','password','token','csrf'].includes(key) && typeof val!=='object').map(([k,v])=>[k, String(v ?? '')]));
      return {No:i+1,...safe};
    });
  }
  function timestampName(report,ext) {
    return 'EduVault_Activity_Report_'+[report.date.getFullYear(),String(report.date.getMonth()+1).padStart(2,'0'),String(report.date.getDate()).padStart(2,'0')].join('-')+'.'+ext;
  }
  async function loadExternal(test,urls) {
    if(test())return;
    let last;
    for(const url of urls){
      try {
        await new Promise((resolve,reject)=>{
          const script=document.createElement('script'); script.src=url;script.async=true;
          script.onload=resolve;script.onerror=()=>reject(new Error('Script download failed'));
          document.head.append(script);
        });
        if(test())return;
      } catch(e){last=e;}
    }
    throw last || new Error('Export library unavailable');
  }
  function pdfLines(doc,text,x,y,maxWidth,lineHeight=6) {
    const parts = doc.splitTextToSize(String(text ?? ''),maxWidth);
    parts.forEach(part => { if(y>278){doc.addPage();y=22;} doc.text(part,x,y);y+=lineHeight; });
    return y;
  }
  async function pdfDownload(report) {
    await loadExternal(()=>!!window.jspdf?.jsPDF,[
      'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.2/jspdf.umd.min.js',
      'https://cdn.jsdelivr.net/npm/jspdf@2.5.2/dist/jspdf.umd.min.js'
    ]);
    const doc=new window.jspdf.jsPDF({unit:'mm',format:'a4'});
    let page=1, y=0;
    function start(title){
      if(page>1)doc.addPage();page++;
      doc.setFillColor(9,19,39);doc.rect(0,0,210,48,'F');
      doc.setTextColor(39,205,224);doc.setFont('helvetica','bold');doc.setFontSize(22);doc.text('EduVault',16,21);
      doc.setTextColor(255,255,255);doc.setFontSize(13);doc.text(title,16,32);
      doc.setTextColor(55,70,90);doc.setFont('helvetica','normal');doc.setFontSize(9);
      doc.text('Generated: '+report.date.toLocaleString(),16,55);y=66;
    }
    function heading(title) {
      if(y>262)start('ACTIVITY OVERVIEW / Details');
      doc.setFillColor(14,93,119);doc.rect(16,y-5,178,9,'F');
      doc.setTextColor(255,255,255);doc.setFont('helvetica','bold');doc.setFontSize(10);
      doc.text(title,19,y+1);y+=13;
    }
    start('ACTIVITY OVERVIEW  |  Management Report');
    heading('REPORT SUMMARY');
    for(const [name,items,source] of report.sections) {
      if(y>277)start('ACTIVITY OVERVIEW / Summary');
      doc.setTextColor(26,46,66);doc.setFont('helvetica','normal');doc.setFontSize(10);
      doc.text(name,19,y);doc.text(String(items.length),180,y,{align:'right'});y+=7;
    }
    y+=4;heading('LIVE DASHBOARD TOTALS');
    for(const [k,v] of Object.entries(report.stats)){doc.setTextColor(26,46,66);doc.setFontSize(10);doc.text(k+': '+(Number.isFinite(v)?v:'Unavailable'),19,y);y+=7;}
    y+=2;heading('LAST 7 DAYS | RECORDED ACTIVITY');
    report.days.forEach((d,i)=>{doc.setTextColor(26,46,66);doc.text(d+': '+report.daily[i],19,y);y+=7;});
    for(const [name,items,source] of report.sections){
      start('DETAILED RECORDS');heading(name.toUpperCase()+' ('+items.length+' records)');
      doc.setFontSize(8);doc.setTextColor(70,88,110);y=pdfLines(doc,'Source: '+source,19,y,170,5);y+=2;
      if(!items.length){y=pdfLines(doc,'No saved records available.',19,y,170);continue;}
      for(const item of rowsFor(items)) {
        doc.setTextColor(24,45,64);doc.setFontSize(9);
        y=pdfLines(doc,Object.entries(item).map(([k,v])=>k+': '+v).join(' | '),19,y,172,5);y+=2;
      }
    }
    const pages=doc.getNumberOfPages();
    for(let i=1;i<=pages;i++){doc.setPage(i);doc.setTextColor(100,113,129);doc.setFontSize(8);doc.text('EduVault | Admin Command Center',16,291);doc.text('Page '+i+' / '+pages,194,291,{align:'right'});}
    doc.save(timestampName(report,'pdf'));
  }
  async function excelDownload(report) {
    await loadExternal(()=>!!window.XLSX?.utils?.book_new,[
      'https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js',
      'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js'
    ]);
    const XLSX=window.XLSX, wb=XLSX.utils.book_new();
    function add(name,rows){
      const data=rows.length?rows:[{'Information':'No saved records available'}];
      const ws=XLSX.utils.json_to_sheet(data);
      const keys=Object.keys(data[0]);
      ws['!cols']=keys.map(k=>({wch:Math.min(65,Math.max(14,k.length+3,...data.slice(0,100).map(r=>String(r[k] ?? '').length+2)))}));
      if(ws['!ref'])ws['!autofilter']={ref:ws['!ref']};
      XLSX.utils.book_append_sheet(wb,ws,name.slice(0,31));
    }
    add('Summary',report.sections.map(([module,items,source])=>({Module:module,Records:items.length,Source:source})));
    add('Live Statistics',Object.entries(report.stats).map(([metric,value])=>({Metric:metric,Count:Number.isFinite(value)?value:'Unavailable'})));
    add('Weekly Engagement',report.days.map((d,i)=>({Date:d,Day:weekDay(d),Activities:report.daily[i]})));
    report.sections.forEach(([name,records])=>add(name.replace(/[^a-z0-9 &_-]/gi,'').slice(0,31),rowsFor(records)));
    XLSX.writeFile(wb,timestampName(report,'xlsx'),{bookType:'xlsx',compression:true});
  }
  function printReport(report,size,orientation,page){
    page=page || window.open('','_blank');
    if(!page)throw new Error('Browser blocked print window. Allow popups for this site.');
    const safeSize=['A4','A5','Letter','Legal'].includes(size)?size:'A4';
    const safeOrient=orientation==='landscape'?'landscape':'portrait';
    const blocks=report.sections.map(([title,records,source])=>'<section><h2>'+esc(title)+' ('+records.length+')</h2><p class="source">Source: '+esc(source)+'</p>'+(records.length?rowsFor(records).map(r=>'<p class="record">'+Object.entries(r).map(([k,v])=>'<b>'+esc(k)+':</b> '+esc(v)).join(' &nbsp; | &nbsp; ')+'</p>').join(''):'<p>No saved records available.</p>')+'</section>').join('');
    const summary=report.sections.map(([name,items])=>'<tr><td>'+esc(name)+'</td><td>'+items.length+'</td></tr>').join('');
    const html='<!doctype html><html><head><meta charset="utf-8"><title>EduVault Management Report</title><style>@page{size:'+safeSize+' '+safeOrient+';margin:14mm}*{box-sizing:border-box}body{font:12px Arial,sans-serif;color:#16243b;margin:0}header{background:#0a152b;color:white;padding:22px}header h1{color:#28c9df;font-size:25px;margin:0}header h2{font-size:17px;margin:8px 0}main{padding:16px}table{border-collapse:collapse;width:100%;margin:18px 0}th{background:#0e203e;color:#fff;text-align:left}td,th{padding:9px;border-bottom:1px solid #d8e0e9}td:last-child{text-align:right}section{break-inside:avoid-page;margin:20px 0}section h2{background:#0d7f98;color:white;padding:10px;font-size:14px}.source{color:#667891}.record{border-bottom:1px solid #e2e7ed;padding:7px 0;overflow-wrap:anywhere}footer{color:#667891;margin:18px 0} @media screen{body{max-width:900px;margin:auto;box-shadow:0 1px 15px #aaa}button{margin:10px;padding:10px}}@media print{.print-btn{display:none}}</style></head><body><header><h1>EduVault</h1><h2>ACTIVITY OVERVIEW | Management Report</h2></header><main><p>Generated: '+esc(report.date.toLocaleString())+'</p><h2>REPORT SUMMARY</h2><table><tr><th>Module</th><th>Records</th></tr>'+summary+'</table><h2>WEEKLY ENGAGEMENT</h2><table><tr><th>Date</th><th>Activities</th></tr>'+report.days.map((d,i)=>'<tr><td>'+esc(d)+'</td><td>'+report.daily[i]+'</td></tr>').join('')+'</table><h2>DETAILED RECORDS</h2>'+blocks+'<footer>EduVault | Admin Command Center</footer><button class="print-btn" onclick="window.print()">Print report</button></main></body></html>';
    page.document.open();page.document.write(html);page.document.close();page.focus();
    setTimeout(()=>{try{page.print();}catch(_){}},450);
  }
  async function takeReport(kind,options) {
    const printPage = kind === 'print' ? window.open('','_blank') : null;
    if(printPage){printPage.document.write('<p style=\"font-family:sans-serif;padding:20px\">Preparing EduVault report...</p>');}
    try {
      status('Preparing latest report...',false);
      const data=await refresh(true);
      const report=reportData(data);
      if(kind==='pdf')await pdfDownload(report);
      else if(kind==='excel')await excelDownload(report);
      else if(kind==='print')printReport(report,options?.size,options?.orientation,printPage);
      status((kind==='print'?'Print preview opened.':kind.toUpperCase()+' report download started.')+(report.unavailable.length?' Some sources are unavailable.':''),false);
    } catch(e) { if(printPage)printPage.close(); console.error('EduVault report export failed:',e);status('Report failed: '+e.message,true); }
  }
  $('report-pdf')?.addEventListener('click',()=>takeReport('pdf'));
  $('report-excel')?.addEventListener('click',()=>takeReport('excel'));
  const dialog=$('print-paper-dialog'), form=$('print-paper-form');
  $('report-print')?.addEventListener('click',()=>{if(dialog){dialog.hidden=false;} else takeReport('print',{size:'A4',orientation:'portrait'});});
  dialog?.querySelectorAll('[data-close-print]').forEach(b=>b.addEventListener('click',()=>dialog.hidden=true));
  if(form)form.addEventListener('submit',e=>{e.preventDefault();const size=$('print-page-size')?.value||'A4',orientation=$('print-orientation')?.value||'portrait';dialog.hidden=true;takeReport('print',{size,orientation});});
})();
