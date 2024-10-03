# scrapeStatsWebradio
scrape listner data from webradio server and store it in a DB


This project take, as input, a list of webradio, with the name, the server url and the type of stream, and it scrapes
number of listner foreach webradio. Data are stored in a database. 

It's suppose to run as cronjob each minute. Add this line to your cronjob. Type:
    
    crontab -e

add this line (modify your path accordingly).

    *  *  *  *  *  php stats.php

Please add a config.json file at root level of your script, similar to this

EXAMPLE

    {
        "dbConfig": {
          "servername": "<DB-HOST>",
          "username": "<DB-USERNAME>",
          "password": "<DB-PASSWORD>",
          "dbname": "<DB-TABLE-FOR-LISTNER>"
        },
        "scheduleUrl\":\"https://www.myradio.com/api-show-schedule"  //optional
        "radios": {
          "<RADIO-NUMBER-1>": {
            "url": "<WEBRADIO-SERVER-URL1>",
            "protocol": "shout",
            "isMyRadio":true      //optional, if scheduleUrl exists, we use this to save show infor
          },
          "<RADIO-NUMBER-2>": {
            "url": "<WEBRADIO-SERVER-URL2>",
            "protocol": "ice",
            "position": 9
          }
      }
    }
