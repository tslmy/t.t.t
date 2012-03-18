#==== Places Where You Should Configure ====
ifbrief=True#  ifbrief=True: tells create.py whether it should make a brief for each file.
iftag=True#  iftag=True: This tells create.py whether it should get a few most common words in each article as tags.

#P.S. Did you notice LinoNote.py? It's my another work. Managing your txt files with it will be super easy. It's Chinese, though.
#You are always welcome to drop by at http://www.tslmy.tk/(where I do website tests) and http://tslmy.sourceforge.net/(where I put my programs). See you! 

from operator import itemgetter
def t(strFileName,amount=10):
    count = {}
    for word in open(strFileName).read().split():
        if count.has_key(word):
            count[word] = count[word] + 1
        else:
            count[word] = 1
    return [i[0]+', ' for i in sorted(count.iteritems(), key=itemgetter(1), reverse=True)[0:amount]]
out=[]
#get text file list START
import os
#now_path=os.getcwd()
all_file=os.popen('dir /OD /b *.txt').read().split('\n')
all_file.reverse()
#get text file list END
for every_file in all_file:
    if not every_file.startswith('_') and every_file.endswith('.txt'):#all txt file whose starts with "_" will be omitted
        out.append(every_file[:-4]+'\n')
        if ifbrief:
            brief_text=''
            fila=open(every_file,'r')
            for each_word in fila.readline().split()[:15]:
                brief_text=brief_text+each_word+' '
            fila.close()
            while len(brief_text)>30:
                brief_text=brief_text[:brief_text.rfind(' ')]
            out.append(brief_text+'...\n')
        else:
            out.append('\n')
        if iftag:
            tags=''.join(t(every_file))[:-2]
            while len(tags)>15:
                tags=tags[:tags.rfind(', ')]
            out.append('('+tags+')\n')
        else:
            out.append('\n')
open('_list.txt','w+').writelines(out)
