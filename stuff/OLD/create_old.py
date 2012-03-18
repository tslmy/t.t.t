#==== Places Where You Should Configure ====
#  _intro.txt
#  <i>Brought to you by Tslmy.</i>: if you love me, keep this.
#  <!-- below to </head>: Google Analytics Code. -->: replace it with your own code!
#  site_name='Lilo^Log' and site_ver
#  ifbrief=True: tells create.py whether it should make a brief for each file.
#  ifphp: tells create.py whether it should link each article to view.php or .txt file itself.
#  ifintro: ...you guess it
#  <a href="http://tslmy.tk">: link this to your main site. you are to use this site as your main site itself? thank you!!
#P.S. Did you notice LinoNote.py? It's my another work. Managing your txt files with it will be super easy. It's Chinese, though.
#You are always welcome to drop by at http://www.tslmy.tk/(where I do website tests) and http://tslmy.sourceforge.net/(where I put my programs). See you! 
site_name='Lilo^Log'
site_ver='0.1^100'
ifbrief=True
ifphp=True
ifintro=True
intro_file="_intro.txt"#strongly suggest to start the file's name with "_" to be omitted in the article list itself!

#get text file list START
import os
#now_path=os.getcwd()
all_file=os.popen('dir /OD /b *.txt').read().split('\n')
all_file.reverse()
#get text file list END

#make intro START
intro=''
if ifintro:
    fila=open(intro_file,'r')
    for each_line in fila.readlines():
        intro=intro+'<p>'+each_line+'</p>'
    fila.close()
#make intro END

f=('''
<!DOCTYPE html>
<html>
<head>
<title>'''+site_name+'  '+site_ver+'''</title>
<link href="style_list.css" rel="stylesheet" type="text/css" />
<!-- below to </head>: Google Analytics Code. -->
<script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-21290300-1']);
    _gaq.push(['_trackPageview']); (function() {
        var ga = document.createElement('script');
        ga.type = 'text/javascript';
        ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl': 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(ga, s);
    })();
</script>
</head>
<body>
<div id="bg"/>
    <div id="left">
        <p id="title">
            <a href="http://tslmy.tk">
                '''+site_name+'''
            </a>
        </p>
        <div id="sub_title">
            '''+intro+'''
        </div>
    </div>
    <div id="main">
            <ul> ''').split('\n')

for every_file in all_file:
    if not every_file.startswith('_') and every_file.endswith('.txt'):#all txt file whose starts with "_" will be omitted
        file_name=every_file[:-4]
        if ifphp:
            link='view.php?name='+every_file
        else:
            link=every_file
        f.append('<li><a href="'+link+'">'+file_name+'</a>')
        if ifbrief:
            brief_text=''
            fila=open(every_file,'r')
            for each_word in fila.readline().split()[:10]:
                brief_text=brief_text+each_word+' '
            fila.close()
            f.append('<br/><div id="brief">'+brief_text+' ...</div>')

f=f+'''
            </ul> 
        <i>Brought to you by Tslmy.</i>
    </div>
</body> 
</html>
'''.split('\n')
fila=open('index.htm','w+')
for line in f:
    fila.write(line+'\n')
fila.close()
