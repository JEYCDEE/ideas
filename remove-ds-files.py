'''
@ Author  : Volodymyr Mon
@ Email   : jeycdee[at]gmail[dot]com
@ License : MIT
@ Systems : Mac OS X / macOS, Linux, Windows not tested

This script searches for all system hidden files (Mac OS X / macOS system
files) and deletes them all from your home subdirectories. They appear while
using samba protocol between Mac OS X / macOS host and Linux guest virtual
machine.
Those files are: .DS_Store, ._.DS_Store, .localized, __MACOSX
'''

import os, getpass, sys

# Files, that will be searched for and deleted. Append this list if you like.
filesToDelete = ['.DS_Store', '._.DS_Store', '.localized', '__MACOSX']

# Class for beautiful x-term colors in question / answers.
class X:
    HEADER = '\033[96m'
    OKBLUE = '\033[94m'
    OKGREEN = '\033[92m'
    WARNING = '\033[93m'
    FAIL = '\033[91m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'
    UNDERLINE = '\033[4m'

# Get current user.
def getUser():
    global user, home

    user = getpass.getuser()
    home = os.path.expanduser('~')

# Get current path, this file stored.
def getPath():
    global path

    path = os.getcwd()


# Define user directory and ask if he want to use this directory or his own.
def defineDirectory():
    global base

    answer = raw_input(
        X.HEADER + X.BOLD +
        "\nYour current directory is " + path + ". Do you want to:\n\n" +
        X.WARNING +
        "1. Use it\n" + 
        "2. Use your home directory\n" +
        "3. Enter your own one\n" +
        X.ENDC +
        "-> "
    )
    
    if answer not in list(['1', '2', '3']): sys.exit()
    if (answer == '1'): base = path
    if (answer == '2'): base = home
    if (answer == '3'): base = answer = raw_input("-> ")

# Search for this system files and add their paths into tuple.
def searchForFiles():
    global fList, fCount
    fList  = []
    fCount = []
    
    for root, dirs, files in os.walk(base):
        for file in files:
            if file in list(filesToDelete):
                fList.append(os.path.join(root, file))
                fCount = str(len(fList))

# Remove all files and exit the program.
def removeAllFilesAndExit():
    for file in fList:
        os.remove(file)

    sys.exit(X.OKGREEN + '\nDeleted. Now it looks cleaner.\n')

# If files were found ask what to do.
def askForAction():
    if not fCount:
        sys.exit(X.OKGREEN + '\nNo files were found, you are lucky.\n')

    action = raw_input(
        X.HEADER + X.BOLD +
        "\nThere are " + 
        X.FAIL + fCount + X.HEADER +
        " file(s), that can be deleted. Would you " + 
        "like to remove all of them? (y/n)\n" + 
        X.ENDC +
        "-> "
    )

    if action.lower() == 'y':
        removeAllFilesAndExit()
    elif action.lower() == 'n':
        sys.exit(X.WARNING + '\nOK, leave them where they are.\n')
    else:
        sys.exit(X.FAIL + '\nCanceled due to incorrect answer.\n')

def exit():
    sys.exit()

# Program's actions in brief.
getUser()
getPath()
defineDirectory()
searchForFiles()
askForAction()
exit()