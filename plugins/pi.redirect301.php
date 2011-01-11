<?php

$plugin_info = array(
  'pi_name' => 'Redirect 301',
  'pi_version' =>'1.1',
  'pi_author' =>'Nick Cernis',
  'pi_author_url' => 'http://nickcernis.com/',
  'pi_description' => 'Redirects to another page or site using the 301 header.',
  'pi_usage' => Redirect301::usage()
  );

class Redirect301
{

  function Redirect301()
  {
		global $TMPL, $FN, $DB, $REGX;
    $permanent = $TMPL->fetch_param('permanent');
    $url = $TMPL->fetch_param('url');
    $external = $TMPL->fetch_param('external');
    $url =  html_entity_decode($url);

    // Get the site's base URL
		if ($external!="yes"){
			$site_url = '';
			$site = ( ! $TMPL->fetch_param('site')) ? '1' :  $TMPL->fetch_param('site');
			if (is_numeric($site)) $query = $DB->query("SELECT site_system_preferences FROM exp_sites WHERE site_id = '".$DB->escape_str($site)."'");
			else $query = $DB->query("SELECT site_system_preferences FROM exp_sites WHERE site_name = '".$DB->escape_str($site)."'");

			if ($query->num_rows > 0) {
				$prefs = $REGX->array_stripslashes(unserialize($query->row['site_system_preferences']));
				$site_url = $prefs['site_url'];
			}

	  }

    switch ($permanent)
		    {
		      case "no":
						if ($external=="yes"){
							Header( "Location:".$url );
					  }else{
							Header( "Location:".$site_url.$url );
					  }
						exit();
		      default:
		        Header( "HTTP/1.1 301 Moved Permanently" );
						if ($external=="yes"){
							Header( "Location:".$url );
					  }else{
							Header( "Location:".$site_url.$url );
					  }
						exit();
		    }

  }

// usage instructions
function usage() {
  ob_start();
?>
-------------------
HOW TO USE
-------------------
Redirect to the specified weblog and template using the SEO-friendly 301 header:
{exp:redirect301 url="weblog/template"}

Redirect to an external site using the 301 header. This is the same as above except
that your site's URL won't be retrieved and prepended automatically.
{exp:redirect301 url="weblog/template" external="yes"}

Redirect without the 301 header:
{exp:redirect301 url="weblog/template" permanent="no"}

If you're using the Multiple Site Manager, simply specify the name or ID of your site, like this:
{exp:redirect301 url="weblog/template" site="site_short_name_or_id"}


-----------------------------------------
SAMPLE REDIRECT TEMPLATE
-----------------------------------------
The redirect301 plugin can be used to create a short link to long post names. Here's how:

1) Create a template group called "go"
2) Edit the index file to include the following code:

{exp:weblog:entries  limit="1" entry_id="{segment_2}" disable="member_data|trackbacks|pagination"}
{exp:redirect301 url="{weblog}/{url_title}"}
{/exp:weblog:entries}

This will take a URL such as http://yoursite.com/go/33/ and issue a 301 redirect to the full URL,
such as http://yoursite.com/articles/you-look-nice-today/


------------------------------
OPTIONAL ADDITIONS
------------------------------
To create a link within your posts that readers can share, simply append the post ID to the
short URL address, like this:

{exp:weblog:entries}
<a href="{site_url}go/{entry_id}/">Short link to this page</a>
{/exp:weblog:entries}

Optionally, you can add the following code between your <head> tags to help spiders determine that
the content is the same. This is good practice but isn't required for the redirect to work.

{exp:weblog:entries}
<link rev="canonical" rel="alternate shorter" href="{site_url}go/{entry_id}" />
{exp:weblog:entries}

<?php
  $buffer = ob_get_contents();
  ob_end_clean();

  return $buffer;
}

}
?>