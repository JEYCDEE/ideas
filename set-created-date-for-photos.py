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

      thisFile = path + "/" + fileName
      newFile  = path + "/" + getDateTaken(thisFile, fileName)

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
if argumentType == "directory" or argumentType == "folder":
  print("Directory mode selected... \n")
  filesList = os.listdir(argumentPath)
  renameFiles(argumentPath, filesList)
