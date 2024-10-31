<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$message = "";

if(isset($_GET['message'])){
	$message = $_GET['message'];
}

if(isset($_GET['delete'])){
	$quote_ids = sanitize_text_field($_GET['quote_ids']);
	$page_ids = sanitize_text_field($_GET['page_ids']);
	$post_ids = sanitize_text_field($_GET['post_ids']);

	$page_idsAA = explode(",", $page_ids);
	foreach($page_idsAA as $page_id){
		update_post_meta($page_id,"pl_quotes","");
	}

	$post_idsAA = explode(",", $post_ids);
	foreach($post_idsAA as $post_id){
		update_post_meta($post_id,"pl_quotes","");
	}

	$wpdb->delete( 'preloader_quotes_mapping', array('id' => sanitize_text_field($_GET['delete'])) );

	$message = "Mapping Deleted Successfully";
}

if(isset($_GET['edit'])){
	$mapping_id = $_GET['edit'];
	$quote_idsA = explode(",",sanitize_text_field($_GET['quote_ids']));
	$page_idsA = explode(",",sanitize_text_field($_GET['page_ids']));
	$post_idsA = explode(",",sanitize_text_field($_GET['post_ids']));
	$random_quotes = sanitize_text_field($_GET['random_quotes']);


}	

$args = array(
	'sort_order' => 'asc',
	'sort_column' => 'post_title',
	'post_type' => 'page',
	'post_status' => 'publish'
); 
$pages = get_pages($args);


$args1 = array(
	'sort_order' => 'asc',
	'sort_column' => 'post_title',
	'post_type' => 'post',
	'post_status' => 'publish'
); 
$posts = get_posts($args1);

$quotes = $wpdb->get_results("select * from preloader_quotes order by id desc");

$pl_quotes = "";

if(!empty($message)){
	echo $message;
}

$mappings = $wpdb->get_results("select * from preloader_quotes_mapping order by id desc");
?>

<div class="clear"></div>
<hr/>
<h3 style="margin-top: 30px;">Add Mapping: </h3>
<form style="border:1px; padding: 20px;width: 50%" id="mapping_form" method="post" name="mapping_form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
	
	<?php wp_nonce_field( 'my_pl_nonce', 'pl_nonce_val' ); ?>
	<input type="hidden" name="action" value="preloader_quotes_mapping" />

	<div class="form-group">
	    <label>Select Page(s)</label>
	    <select class="form-control" multiple="multiple" name="pl_page[]">
			<option value="">Select Page</option>
			<?php
				foreach($pages as $page){
					?>
					<option <?php echo in_array($page->ID, $page_idsA)?'selected':''; ?> value="<?php echo $page->ID ?>"><?php echo $page->post_title; ?></option>
					<?php 
				}
			?>
		</select>
	</div>


	<div class="form-group">
	    <label>Select Post(s)</label>
	    <select class="form-control" multiple="multiple" name="pl_post[]">
			<option value="">Select Post</option>
			<?php
				foreach($posts as $post){
					?>
					<option <?php echo in_array($post->ID, $post_idsA)?'selected':''; ?> value="<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></option>
					<?php 
				}
			?>
		</select>
	</div>
	

	<div class="form-group">
	    <label>Select Quote(s)</label>
	    <select style="display: inline-block;width: 50%" class="form-control" id="pl_quote">
			<option value="">Select Quote</option>
			<?php
				foreach($quotes as $quote){
					?>
					<option value="<?php echo $quote->id; ?>"><?php echo $quote->quote; ?></option>
					<?php 
				}
			?>
		</select>
		<input class="btn btn-success" type="button" id="add_quote" value="Select Quote" >
	</div>

	<br/>

	<label>Selected Quotes (reorder):</label>
	<ul id="sortable">
	  
	</ul>

	<br/>

	<label>Random? </label>
	<input type="checkbox" name="random_quotes" value="1" <?php echo $random_quotes == '1'?'checked':''; ?> />

	<input type="hidden" id="pl_quotes" name="pl_quotes" value="" />

	<input type="hidden" id="mapping_id" name="mapping_id" value="<?php echo $mapping_id; ?>" />
	<br/>
	<button class="btn btn-primary" name="save_mapping" id="save_mapping">Save</button>
</form>

<hr/>

<h3>Mapping List: </h3>
<table class="table table-striped">
	<thead class="thead-dark">
		<tr>
			<th>Quotes</th>
			<th>Pages</th>
			<th>Posts</th>
			<th>Random?</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		foreach($mappings as $mapping){
			$quote_ids = $mapping->quote_ids;
			$page_ids = $mapping->page_ids;
			$post_ids = $mapping->post_ids;
			
			$quotes_data = $wpdb->get_results("select * from preloader_quotes where id in (".$quote_ids.")");

			$quotes_names = "";
			$quote_idsA = explode(",",$quote_ids);
			foreach($quote_idsA as $quote_id){
				foreach($quotes_data as $quote_data){
					if($quote_id == $quote_data->id){
						$quotes_names .= $quote_data->quote.",";
						break;
					}
				}
			}

			
			

			$post_table = $wpdb->prefix."posts";
			$pages_data = $wpdb->get_results("select * from $post_table where id in (".$page_ids.")");

			$pages_names = "";
			foreach($pages_data as $page_data){
				$pages_names .= $page_data->post_title.",";
			}

			$posts_data = $wpdb->get_results("select * from $post_table where id in (".$post_ids.")");

			$posts_names = "";
			foreach($posts_data as $post_data){
				$posts_names .= $post_data->post_title.",";
			}
			?>
			<tr>
				<td><?php echo rtrim($quotes_names,","); ?></td>
				<td><?php echo rtrim($pages_names,","); ?></td>
				<td><?php echo rtrim($posts_names,","); ?></td>
				<td><?php echo $mapping->random_quotes == 1?'yes':'no'; ?></td>
				<td>
					 <a href="<?php echo admin_url().'admin.php?page=preloader-quotes-mapping&edit='.$mapping->id ?>&quote_ids=<?php echo $quote_ids; ?>&page_ids=<?php echo $page_ids ?>&post_ids=<?php echo $post_ids ?>&random_quotes=<?php echo $mapping->random_quotes; ?>">Edit</a>

					|<a href="<?php echo admin_url().'admin.php?page=preloader-quotes-mapping&delete='.$mapping->id ?>&quote_ids=<?php echo $quote_ids; ?>&page_ids=<?php echo $page_ids ?>&post_ids=<?php echo $post_ids ?>">Delete</a>
				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>

<script>
  jQuery( function($) {
    $( "#sortable" ).sortable();
    $( "#sortable" ).disableSelection();
  });

  jQuery(document).ready(function($){
  	$('#add_quote').click(function(){
  		var selected_quote_id=$('#pl_quote').val();
  		var selected_quote_text = $('#pl_quote option:selected').text();
  		if(pl_quote === ""){
  			alert("Please Select Quote");
  			return false;
  		}

  		var li = '<li class="ui-state-default" id="'+selected_quote_id+'"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>'+selected_quote_text+'</li>';

  		$('#sortable').append(li);
  	});

  	$('#save_mapping').click(function(e){
  		e.preventDefault();
  		var quote_ids = "";
  		$.each($('#sortable').find('li'), function() {
	        quote_ids += $(this).attr('id')+",";
	    });
	    quote_ids = quote_ids.replace(/,\s*$/, "");
	    $('#pl_quotes').val(quote_ids);

	    $('#mapping_form').submit();
  	});
  });
  </script>

  <?php 
	if(isset($_GET['edit'])){
		$quote_idsA = explode(",",$_GET['quote_ids']);
		foreach($quote_idsA as $quote_id){
			if(empty($quote_id) || is_null($quote_id) || $quote_id == 'null'){
				continue;
			}
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($){
					$('#pl_quote').val('<?php echo $quote_id ?>')
					$('#add_quote').trigger('click');
				});
			</script>
			<?php 
		}
	}

	?>
