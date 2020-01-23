# scrapeStatsWebradio
scrape listner data from webradio server and store it in a DB


This project take, as input, a list of webradio, with the name, the server url and the type of stream, and it scrapes
number of listner foreach webradio. Data are stored in a database. 

It's suppose to run as cronjob each minute. Add this line to your cronjob. Type:
    
    crontab -e

add this line (modify your path accordingly).

    *  *  *  *  *  php stats.php