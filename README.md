# tumblr-chat-messages-downloader

A script to download tumblr chat messages
```
Usage:
./tumblr.php [--skip-cert-verify] -u username -p password -b blog [-i partner] [-c conversation] [-f filename] [-s] [-d YYYYMMDD] [-r [req/sec]]
```
There two ways to fetch conversations:

New easy way is to specify username, password, blog and partner by username

Old way is to run script first only with your Tumblr username, password and the URL for the specific blog **without** the ".tumblr.com" portion. (The blog must be associated with your Tumblr account.)

You will get a list of all available conversations, which will appear as follows:

```
111111111 username <=> chatuser1
222222222 username <=> chatuser2
333333333 username <=> chatuser3
...
```

Once you have acquired this list, run the script again, this time using the number of the conversation you want to download: 
```
Usage: ./tumblr.php -u username -p password -b blog -c 111111111
```
Messages will be displayed in the terminal. 

If you want the messages to be saved to a file, use a file name, as shown below:
```
Usage: ./tumblr.php -u username -p password -b blog 111111111 -f file.txt
```
On some installs local cert authority is not up to date and if you receive empty page, you can try to skip SSL verification with --skip-cert-verify option. Keep in mind that this is INSECURE.

if **-r** option is supplied then script will wait some time to meet rate limit requests per minute (default 1000 if not specified)

If **-s** option is supplied log file will be splited in multiple files (one for each day) and date will be added to file name (file-YYMMDD.txt).

If **-d** option is supplied then only chat messages from specified date will be downloaded (YYYYMMDD format).

WARNING: If the filename you have chosen already contains content, its original contents will be *overwritten without warning*.

This script has not yet been tested very well, so it may have bugs, or not work at all. ~~For instance, it handles cases where the blog password is incorrect very badly, as there are no relevant checks.~~

Full help message (displayed if no or invlid options are supplied):
````
Usage: ./tumblr.php [--skip-cert-verify] -u username -p password -b blog [-i partner] [-c conversation_id] [-f filename] [-s] [-d YYYYMMDD] [-r [req/sec]]

    There two ways to fetch conversations:

    New easy way is to specify username, password, block and partner by username

    Old way is to run script first only with username, password and blog to get list of conversations.
    Then run script again specifying conversation you want to download.

    -u, --username (required)
	tumblr username or E-mail

    -p, --password (required)
	tumblr password

    --skip-cert-verify
	skip SSL verification - INSECURE!

    -b, --blog (required)
	tumblr blog without .tumblr.com (required)

    -i, --partner (optional)
	Instead of using conversation id this is a new way
	to fetch conversation by specifying partner username

    -c, --conversation_id (optional|required)
	conversation id from the list

    -r, --rate-limit [requests] (optional [optional=1000])
	set rate limit per minute - default 1000 if no value specified

    -d, --date YYYYMMDD (optional)
	output only log for specified date

    -f, --file filename (optional)
	output file name

    -s, --split (optional) (require -f)
	put output in separete files for each day: filename-YYYYMMDD.ext
````
