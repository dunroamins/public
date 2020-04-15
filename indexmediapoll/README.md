indexmediapoll
======
This is a simple poll where only unique visitors can vote on this poll (unique IP address)
To setup:
1. Run indexmedia.sql in any database created
2. Must have an AWS S3 account with key and secret
3. Update include/Config.php by replacing all '<...>' with valid information
4. Set DOCUMENT_ROOT to html folder
5. Change php.ini to have include folder in include path i.e for windows server: include_path = ".;C:\PHP\indexmediapoll\include\"
6. Take poll
