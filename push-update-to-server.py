import os
import time

def git_commit_change():
    l = os.popen('git status').readlines()
    l = [line.lstrip('#') for line in l]
    s = ''.join(l)
    if not 'nothing to commit' in s:
        if 'deleted:' in s:
            for line in [line for line in l if 'deleted' in line]:
                os.system('git rm %s'%line.strip().split(' ')[-1:][0])
        commit = [line for line in l if line.startswith("\t")]
        for i, line in enumerate(commit):
            if ':' not in line:
                commit[i] = "\tnew file:   %s\n"%line.strip()
        commit = ''.join(commit).rstrip()
        os.system('git add .')
        os.system('git commit -m "%s"'%commit)
        #os.system('git branch %s'%time.strftime("%Y-%m-%d_%H_%M_%S"))

def git_push_2_server():
    os.system('git push bbs master')

if __name__ == '__main__':
    git_commit_change()
    #git_push_2_server()
