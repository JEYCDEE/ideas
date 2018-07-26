'''
@ Author  : Volodymyr Mon
@ License : MIT
@ Systems : Mac OS X / macOS, Linux, Windows not tested

This script let you select either one file or whole directory with
images/photos, analyzie their creation/modification/access date
and rename one or all files with the timestamp. After all you
should see something like 20180614_220000.jpg.

Usage for files:
python set-created-date-for-photos.py file /path/to/a/single/file.jpg

Usage for directories:
python set-created-date-for-photos.py folder /path/to/a/directory

'''

from PIL import Image
import os
import sys
import datetime

if len(sys.argv) <= 2:
  print("Not enough arguments")
  sys.exit()

# This is a operation trigger, could be file, folder or directory.
argumentType    = sys.argv[1]

# Path to the file or whole directory.
argumentPath    = sys.argv[2]

# This is a identificator for date created exif attribute.
DATE_CREATED    = 36867

# How you'd like to join date and time?
SEPARATOR       = "_"

# Print exception errors
SHOW_EXCEPTIONS = True

# Print result for each operation
SHOW_OPERATIONS = True

# Print status (success, warning, fail) for each operation.
# Success: exif date of creation rename successful
# Warning: date of modification has been set
# Error: file has not been renamed
SHOW_STATUSES   = True

# This is a list of system files, we would not want to rename.
SYSTEM_FILES = ('.ini', '.sys', '.conf')

# Retrieve either the date shot has been taken, or at least date
# of it's modification or access. For example .png does not have
# any exif data. If none of dates exist, return original name.
def getDateTaken(path, oldName = ''):

  try:

    dateTaken = Image.open(path)._getexif()[DATE_CREATED]
    extension = (os.path.splitext(path)[1]).lower()
    result    = dateTaken.replace(" ", SEPARATOR)
    result    = result.replace(":", "") + extension

    if SHOW_STATUSES:
      print("Success: %s" % path)

    return result

  except Exception as e:

    if SHOW_STATUSES:
      print("Warning: %s" % path)

    if SHOW_EXCEPTIONS:
      print("Get Date Take exception: %s. Setting other Date" % str(e))

    dateModified = os.stat(path).st_mtime
    dateCreated  = os.stat(path).st_ctime
    dateAccess   = os.stat(path).st_atime
    extension    = (os.path.splitext(path)[1]).lower()

    if dateModified == None and dateCreated == None and dateAccess == None:
      if SHOW_STATUSES:
        print("Error: %s" % path)
      return oldName

    if dateModified != None:
      result = datetime.datetime.fromtimestamp(dateModified)
    elif dateCreated != None:
      result = datetime.datetime.fromtimestamp(dateCreated)
    elif dateAccess != None:
      result = datetime.datetime.fromtimestamp(dateAccess)

    result = result.strftime("%Y%m%d %H%M%S").replace(" ", SEPARATOR)
    result += extension

    return result

# Rename only one given file
def renameFile(path, fileName):

  try:

    thisFile = path + "/" + fileName
    newFile  = path + "/" + getDateTaken(thisFile, fileName)

    os.rename(thisFile, newFile)

  except Exception as e:

    if SHOW_EXCEPTIONS:
      print("Rename File excpetion: " + str(e))

# Iterate through each list object and apply actions to all
# of them.
def renameFiles(path, filesList):

  try:

    for fileName in filesList:

      thisFile       = path + "/" + fileName
      newFile        = path + "/" + getDateTaken(thisFile, fileName)
      isSubDirectory = os.path.isdir(path + "/" + fileName)
      fileExtension  = (os.path.splitext(path + "/" + fileName)[1]).lower()

      if isSubDirectory is True: continue
      if fileExtension in SYSTEM_FILES: continue

      os.rename(thisFile, newFile)

      if SHOW_OPERATIONS:
        print(thisFile + "\n" + newFile + "\n")

  except Exception as e:

    if SHOW_EXCEPTIONS:
      print("Rename Files excpetion: " + str(e))

# Trigger file function
if argumentType == "file":
  print("File mode selected... \n")
  path     = os.path.dirname(argumentPath)
  fileName = os.path.basename(argumentPath)
  renameFile(path, fileName)

# Trigger directory function
if argumentType == "directory" or argumentType == "folder" or argumentType == "dir":
  print("Directory mode selected... \n")
  filesList = os.listdir(argumentPath)
  renameFiles(argumentPath, filesList)
