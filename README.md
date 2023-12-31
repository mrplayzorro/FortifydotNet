# FortifydotNet
Hello, I've created FortifydotNet to protect websites from any type of DDoS attack (for a school project, xd). It's really easy to integrate, it's open source and free.

## Security Measure 1
It checks all IPs that are getting connected to the website, it determines the general location (for caching and security purposes), it then checks if this IP is doing more than X amount of requests per second, if it does then it blocks it. I've also made an optional captcha that can be enabled or disabled. 

## Security Measure 2
It checks how many IPs are getting connected to the website, if it's more than what the owner of the website has put in the config files it will prompt them a captcha to a separate server. (Please note that this feature might not work in bigger websites).

## Security Measure 3
It checks and blocks any VPNs (there might be false positives), proxies and Spoofed IPs. 

## Security Measure 4 
From security measure 1 it gets the countries and makes a database, showing the countries that connected there the most, how many times they did. This is to give the site owner a general idea on who might be DDoSSing them. 
