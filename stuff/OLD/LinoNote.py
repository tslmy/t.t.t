# -*- coding: cp936 -*-
#[[[[[TODO]]]]]
#调整listbox的3d属性
#使listbox总是占30%百分比的宽度
#===隐藏控制台窗口
import ctypes
whnd = ctypes.windll.kernel32.GetConsoleWindow()
if whnd != 0:
    ctypes.windll.user32.ShowWindow(whnd, 0)
    ctypes.windll.kernel32.CloseHandle(whnd)
#======

help_text=u'将程序拷贝到一个含有.txt文件的文件夹下，然后启动LinoNote.exe。\n在左侧的笔记清单中选择笔记，在右侧的编辑区内编辑。\n当退出程序或选择其它文件时，笔记会被自动保存。\n编辑区顶部是标题栏，可以对文件进行更名和文件夹转移。请注意，文件夹的“\\”符号请均用“>”来表示，并且无需为文件添加扩展名。\n在“<New Note>”这项笔记中，您可以直接创建新的笔记。\n请联系tslimingyang@126.com来获取更多信息，或关注http://tslmy.tk。\n'

version='LinoNote 1.0'
new_note_title='<New Note>'
jinzhishixiang=['?','/','*',':','“','<','>','|']
import Tkinter as tk#装载一个跨平台、流行的GUI库：Tkinter
import tkMessageBox#装载对话框库（tkMessageBox）给“关于”用
import Tix as tx
from time import strftime#装载时间戳工具
import md5#装载MD5库
def getsignature(contents): #得到MD5码
    return md5.md5(contents).digest()
import os #装载os库
root = tk.Tk()#定义主窗口
root.title(version)#   此方法可以更改窗口标题
root.geometry('800x400')#   设置主窗口的初始大小
#   菜单栏
#（空func:   lambda : None
menu = tk.Menu(root)
root.config(menu=menu)
#filemenu.add_separator()
helpmenu = tk.Menu(menu)
menu.add_cascade(label=u"帮助", menu=helpmenu)
helpmenu.add_command(label=u"使用方法…", command=lambda : tkMessageBox.showwarning(u'使用方法',help_text))
helpmenu.add_command(label=u"关于…", command=lambda : tkMessageBox.showwarning(u'关于',u'林诺笔记软件，由李名扬制作。'))
#label的使用方法：
#   w = tk.Label(master=top_frame, text="Hello, world!")
#   w.pack(side=tk.LEFT)
#按钮的使用方法：
#   button = tk.Button(top_frame, text="QUIT", fg="red", command=root.quit)
#   button.pack(side=tk.RIGHT)
#frame，仅是一个容器。使用方法如下：
#   frame = tk.Frame(root)
#   frame.pack(side=tk.TOP)
#本程序中，分左、右两部分：
#   左
left = tk.Frame(root)
left.pack(side=tk.LEFT,ipadx='40',fill='y') #其实这里即使fill='both'也会因为side的存在的阻碍而使得效果等同于fill='y'
#   左偏右的scrollbar的所在区域
l_bar = tk.Frame(left)
l_bar.pack(side=tk.RIGHT,fill='y')
#   右
right = tk.Frame(root)
right.pack(expand='yes',fill='both')
#   右偏右的scrollbar的所在区域
r_bar = tk.Frame(right)
r_bar.pack(side=tk.RIGHT,fill='y')
#元件的装载：
#   对于左边：
#       垂直滚动条1：
scrollbar_l = tk.Scrollbar(master=l_bar)
scrollbar_l.pack( side = tk.RIGHT,padx=3, fill=tk.Y ) #padx:thick board on X direction, pixels
#       LISTBOX：
listbox = tk.Listbox(relief=tk.SUNKEN,borderwidth=1,font='宋体 11',master=left,selectmode=tk.BROWSE,yscrollcommand = scrollbar_l.set)
listbox.pack(expand='yes',fill='both') #expand='yes',fill='both' will make it fill the whole container
scrollbar_l.config( command = listbox.yview )#hook上scrollbar
#   对于右边：
#       一个Entry框，用来显示文件名（就是标题了啦）
biaoti=tk.Entry(master=right)#text 参数无用
biaoti.pack(side=tk.TOP,fill='x')#默认side=tk.TOP
#       垂直滚动条2：
scrollbar_r = tk.Scrollbar(master=r_bar)
scrollbar_r.pack( side = tk.RIGHT ,padx=0, fill=tk.Y ) #padx:thick board on X direction, pixels
#       文字编辑部分：
t=tk.Text(master=right)
t.pack(expand='yes',fill='both')#expand='yes'将会关掉side的效果，以使fill能够在两个方向上作用
t.config(state=tk.NORMAL,font='宋体 12',yscrollcommand=scrollbar_r.set)#font setting
scrollbar_r.config( command = t.yview )#hook上scrollbar
#核心部分：
#   全局属性
cur_file=''#“目前所打开的文件”是“当前应有标题”的扩展
cur_name=''
cur_md5=''#用来存放刚打开文件时的MD5值
cur_index=listbox.index(tk.ACTIVE) #存储目前所打开文件的索引位置

def get_folder(fila):
    last_sprtor=fila.rfind('\\')
    if last_sprtor==-1:
        folder=''
    else:
        folder=fila[:last_sprtor]
    print 'folder for '+fila+' is:'+folder
    return (folder)
##def folderize(fila): 已经被合并到了file_save中。
##    folder=get_folder(fila)
##    if not os.path.isdir(folder):
##        print os.popen('md '+folder).read()
def file_save(file_path,context):
    folder=get_folder(file_path)
    if not os.path.isdir(folder):
        print os.popen('md "'+folder+'"').read()
    fila=open(file_path,'w+')
    fila.write(context.encode('GBK')[:-1])# [:-1] will get rid of a additional \n created by encode()
    fila.close()
    print 'File Saved to '+file_path #这只在控制台中出现


listbox.insert(tk.END,new_note_title)
#   加入文件条目
files = os.popen('dir /b /s *.txt').readlines()
if len(files)==0:
    file_save(u"使用指南.txt",help_text)
    listbox.insert(tk.END,u'使用指南')
else:
	for item in files:
		listbox.insert(tk.END, item[len(os.getcwd())+1:-5].decode('GBK').replace('\\','>'))#我们都是GBK编码的！
    #       os.getcwd()用来得到当前python的工作路径；+1是删除多余的“\\”；-1是删除多余的换行符（因为是readlines嘛。。。不过，好像没这个也可以）。
    #       add a entry "aaa" at the END of the listbox: listbox.insert(tk.END, "aaa")



def if_folder_empty(folder):
    if folder<>'':
        print os.listdir(folder) #DEBUG
        if len(os.listdir(folder))==0: #如果空了
            os.popen('rd '+folder.encode('GBK'))#删除文件夹
            print 'Deleted folder: '+folder
    else:
        print 'This file is saved on the root folder, which means, souce folder can not be deleted.'
def cur_del():
    listbox.delete(cur_index)#在列表中删掉它
    os.popen('del '+cur_file.encode('GBK'))
    print 'Deleted: '+cur_file
    if_folder_empty(cur_dir)
def working_get():
    global working_title,working_text,working_file,working_dir,working_md5
    working_title=biaoti.get().encode('UTF-8')
    for i in jinzhishixiang:
        working_title.replace(i,'')
    working_title=working_title.decode('UTF-8')
    working_text=t.get(1.0, tk.END)
    if working_title==new_note_title and working_text=='\n':
        working_file=''
        working_dir=''
    else:
        working_file=working_title.replace('>','\\')+'.txt'
        working_dir=get_folder(working_file)
    working_md5=getsignature(working_text.encode('GBK'))
def push(what,where):
    lisa=listbox.get(0,tk.END)
    for i in lisa:
        if i==what:
            return 0
    listbox.insert(where,what)
def save_note(ifpush): #真正的主程序
    global working_title
    if cur_index==0 :
        print '正在离开 新建笔记 功能'
        if working_text=='\n':
            print '没有内容 不保存笔记'
        else:
            print '开始保存新笔记……'
            if working_title==new_note_title:
                working_title=strftime("Unsorted>%y-%m-%d_%H%M%S")
                print '御坂自动给出了标题：'+working_title
            file_save(working_title.replace('>','\\')+'.txt'.encode('GBK'),working_text)#p.s. “当前标题”已被净化！
            if ifpush:
                push(working_title,1)
    else:
        print '离开了一项普通笔记。'
        if working_title<>'' and working_text<>'\n': #如果不要删除文件
            print '并不是要删除文件（没有一处留空）'
            if working_file<>cur_file:#如果改了文件名或路径
                print '改了文件名或路径，视为移动'
                if not os.path.isdir(working_dir):
                    print '新建了文件夹：'+os.popen('md '+working_dir.encode('GBK')).read()
                print os.popen('move "'+cur_file.encode('GBK')+'" "'+working_file.encode('GBK')+'"').read()
                print "   查询源文件夹是否为空……"
                if_folder_empty(cur_dir)
                if ifpush: #通过判断ifpush来确定（是从哪个事件里来的）和（是否需要操作列表）。若需要操作列表，必定不是！且不能是！从“退出”事件中来。因为index会出现问题。
                    if push(working_title,cur_index)==0:
                        print '列表中已有相同条目，不会增入。'
                    else:
                        print '已加入列表首端。'
                listbox.delete(cur_index+1)#在列表中删掉
                print '删除了旧的条目：第'+str(cur_index+1)+'项。'
            if cur_md5<>working_md5: #（如果不要删除文件，且内容被更改了）
                print '虽然不要删除文件，但是还更改了内容——视为对文件的修改，保存文件…'
                file_save(working_file,working_text)#就必须要保存一次。用现在给出的文件路径
        else:#如果要删除文件
            cur_del()
def on_exit_save():# 一个把原save分开后的产物，同样，这个函数中也不应出现对now_集的操作
    working_get()
    try:
        save_note(False)
    except:
        print 'A error has occccured.'
    finally: #放在finally里，以免关不掉窗口
        root.quit()#因为是由"WM_DELETE_WINDOW"传出的。所以帮个忙，关闭窗口
def chose_a_file(event): #ignore event......这个函数，专管选择了listbox中的一项之后，对各种环境变量的操作
    print '事件>点选了东西哦。'
    #scrollbar_l.set((listbox.index(tk.ACTIVE) / listbox.index(tk.END)),0)
    scrollbar_l.config( command = listbox.yview )
    global cur_index,cur_name,cur_file,cur_dir,cur_md5,working_title,working_text,working_file,working_dir,now_index     #全局化：从哪儿来 指针
    cur_index=listbox.index(tk.ACTIVE)	#得到 从哪儿来 指针
    now_index=listbox.curselection()[0] #只有一个，所以是[0]
    #得到目前工作区的内容
    working_get()
    if now_index=='0': #如果是来到了“新建笔记”这里的话
        print '进入 新建笔记 功能'
        now_name=new_note_title #新的名字
        now_file='' #文件路径设为空
    else:
        now_name=listbox.get(now_index) #这个名称不需要被禁止事项过滤器净化，因为列表中的都被严格净化过
        now_file=now_name.replace('>','\\')+'.txt'
        print '点选在了：'+now_index
        print 'jumped from '+str(cur_index)+' to '+ str(now_index)
        save_note(True)
    print 'Resetting to next turn...'
    #开始准备下一轮
    cur_file=now_file #将当前文件转为单击着的文件
    print 'Currently opening file: '+str(cur_index)+'. '+cur_file+'('+cur_name+')'
    cur_name=now_name
    cur_index=now_index
    print 'New source folder: ',
    if now_index=='0': #如果进入了 新建笔记 模块
        cur_dir=''
        print '<Now note> Function, no folder is included.'
    else:
        cur_dir=get_folder(cur_file)#这里的cur_file实际上就是下一轮所选中的文件——now_file。

    print '======================================'
#标题栏
    biaoti.delete(0, tk.END)#清空标题栏
    biaoti.insert(0, cur_name)#插入当前文件名
    root.title(cur_name+' - '+version)#更改窗口标题
    t.delete(1.0, tk.END) #文字框更改
#   文件操作 - 读取
    if now_index<>'0': #若不是去新建笔记
        fila=open(cur_file) #打开文件
        t.insert(tk.END,fila.read().decode('GBK')) #还是那句老话，我们都是GBK的。。。
        fila.close() #关闭文件
        cur_md5 = getsignature(t.get(1.0, tk.END).encode('GBK')) #得到MD5码
    #else:
        #cur_md5 = '' 这句也没用
        #文字框留空即可
    print '等待用户动作...>'


#初始化
biaoti.insert(0,new_note_title)	#将 新建笔记 界面 加入标题
listbox.itemconfig(0, bg='light grey', fg='white') 
listbox.selection_set(0) #使其成为默认，以便方便 开启后直接编辑
#       把事件与函数hook上
listbox.bind('<ButtonRelease-1>',chose_a_file)  #将 选择条目 这个事件 与 chose_a_file 函数 关联上
root.protocol("WM_DELETE_WINDOW", on_exit_save) #将 关闭窗口 这个事件 与 on_exit_save 函数 关联上。注意，这两个函数的关联方式不同。
t.focus_set() #使文字编辑区域得到焦点
root.mainloop() #进入主循环
