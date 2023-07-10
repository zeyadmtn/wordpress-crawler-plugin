# Problem
According to the user story, the admin would like to see how their website links are linked together in their homepage to find ways to optimize their SEO.

# Solution
To build a web crawler plugin, which allows the admin to press a trigger button, which triggers a web crawler than crawls all the links that are linked in the homepage, display these results to the admin in the plugin page, and create a sitemap page to show how the website is connected. The sitemap is available for all users to access.

# Technical Overview
The plugin will display the crawl trigger button, and the crawl results in the admin page. Once the admin clicks on the crawl trigger button, the crawler will perform the following actions:
- Delete the sitemap.html if it exists.
- Delete any records of previous crawl results.
- Crawl the homepage for all links, store them in MySQL database table "wp-crawl-results" as 'name' and 'url'.
- Store the homepage as an HTML file.
- Create a new sitemap.html file, which displays the crawl results in a list structure format, with name being the name of the link, which is hyperlinked to the URL stored.
- Set all of these actions above to run as a job/event every hour using WordPress Cron.

# Code Implementation
- The code checks if the sitemap.html exists, if it does, it deletes it.
- Checks if the wp-crawl-results table has entries, if it does, it truncates the table.
- The crawler loads up the home page HTML as a DOM Document, where it then can access all elements in the file.
- It then finds all anchor tag elements (<a></a>), then retrieves the 'href' and text value inside the element to extract the 'name' and 'url' which we are looking for. The URL is parsed appropriately before being stored.
- It then stores each entry in the wp-crawl-results table.
- The crawler so far has exception handling set in place, and calls on the displayErrorMessage function to display certain error messages based on the error that occured. For example, if the retrieved name and URL are irregular and cannot be entered into the database for some reason, or if the home page cannot be loaded as an html DOM Document, etc. Simple error messages are given, with suggestions. 
- The code then stores the home page php file as an html file using the file_get_contents helper function.
- Finally, it creates the sitemap.html file, which fetches the crawl results from the database table "wp-crawl-results", then displays the results in a list structure, where each page has it's list of links, but in this case, only the home page. Each link is clickable and is displayed as it's name and not the URL. The title of the sitemap also includes a hyperlink back to the homepage incase the user would like to return.

# Outcome
This solution satisfies the admin's request, as now they are able to see all the hyperlinks connected to their website's homepage, and trace them. As an addition, a sitemap page is also created for his users to see, incase they would like more details on navigating the website.

# Final Thoughts
This solution is tailored to the admin's request and needs, however, changes would need to be made if the crawler hsa a bigger scope. For example, if the admin wanted to crawl every single link, then the code would need to be changed to accodomate accordingly, as well as the database. For example, the database would change structure to accomodate storing relationships between links in a parent-child relationship. The code would need to be adjusted to display this tree list accordingly, and the admin would probably require more information displayed to better understand the displayed tree structure. In addition, more detailed exception handling would be required as the crawler then would crawl multiple pages.

In addition, for implementation to be in production-level, the crawler would probably have to watch for changes in the homepage and re-run everytime changes in the homepage is made, and re-create the sitemap list structure.

The way I approached this solution is step by step in the following order:
- Build a working plugin and activate it to make sure it runs on the admin side.
- Create the basic crawler to make sure the crawling process works, and connection and queries with the database work.
- Create the basic sitemap creation implementation and make sure the 'infrastructure' of the solution is up and working.
- The rest of the work is done in an iterative process, going back to the admin requirements, and fine tuning and implementing each requirement, testing each change, and repeat until the implementation is finalized.