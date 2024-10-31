<?php 
if ( ! defined( 'ABSPATH' ) ) exit;

$quote = "";
$author = "";
$template = "";
$quote_id = "";
$message = "";
$global_quote = get_option('pl_quote_global',true);

global $wpdb;


if(isset($_GET['message'])){
	$message = $_GET['message'];
}

if(isset($_GET['quote_id'])){
	$quote_id = sanitize_text_field($_GET['quote_id']);

	if($_GET['action'] === 'edit'){
		$quote_row = $wpdb->get_row("select * from preloader_quotes where id=".$quote_id);
		$quote = $quote_row->quote;
		$author = $quote_row->author;
		$template = $quote_row->template;
	}
	elseif($_GET['action'] === 'delete'){
		$wpdb->delete( 'preloader_quotes', array('id' => $quote_id) );
		$message = "Quote Deleted Succesfully";
	}
}

$quotes = $wpdb->get_results("select * from preloader_quotes order by id desc");

$global_quote = get_option('pl_quote_global',true);

if(!empty($message)){
	echo $message;
}
?>

<div class="clear"></div>
<hr/>
<h3 style="margin-top: 30px;">Add Quote: </h3>
<form style="border:1px; padding: 20px;width: 50%" method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">

	<?php wp_nonce_field( 'my_pl_nonce', 'pl_nonce_val' ); ?>
	<input type="hidden" name="action" value="preloader_quotes" />

	<div class="form-group">
	    <label>Quote</label>
	    <textarea  class="form-control" required="required" placeholder="Enter your quote here..." name="pl_quote"><?php echo $quote; ?></textarea>
	</div>

	<div class="form-group">
	    <label>Author</label>
	    <input type="text" class="form-control" placeholder="Author Name" name="pl_quote_author" value="<?php echo $author; ?>">
	</div>

	<div class="form-group">
	    <label>Template</label>
	    <select name="pl_quote_template" class="form-control">
	    	<option <?php echo $template == '1'?'selected':''; ?> value="1">Template 1</option>
	    	<!-- <option <?php echo $template == '2'?'selected':''; ?> value="2">Template 2</option>
	    	<option <?php echo $template == '3'?'selected':''; ?> value="3">Template 3</option>
	    	<option <?php echo $template == '4'?'selected':''; ?> value="4">Template 4</option>
	    	<option <?php echo $template == '5'?'selected':''; ?> value="5">Template 5</option> -->
	    </select>
	</div>

	<div style="margin-left: -17px;" class="form-check">
	    <input class="form-check-input" type="checkbox" name="pl_quote_global" value="1" <?php echo $global_quote==$_GET['quote_id'] ? 'checked':''; ?> />
	    <label style="margin-top: -19px;margin-left: 30px;" class="form-check-label" >Make it global</label>
	</div>

	<input type="hidden" name="quote_id" value="<?php echo $quote_id; ?>">
	<button type="submit" class="btn btn-primary" name="save_pl_quote">Save</button>
</form>


<hr/>

<h3 style="display: inline-block;">Global Quote : </h3>
<?php 
$global_quote_name = "";
$global_author_name = "";
$global_template_name = "";
foreach($quotes as $quote){
	if($quote->id==$global_quote){
		$global_quote_name = $quote->quote;
		$global_author_name = $quote->author;
		$global_template_name = $quote->template;
	}
}
if(empty($global_template_name)){
	$global_template_name = 1;
}
echo "<br/><b>Quote:</b> ".$global_quote_name."<br/>";
echo "<b>Author:</b> ".$global_author_name."<br/>";
echo "<b>Template:</b> ".$global_template_name."<br/>";

?>

<hr/>

<h3>Quotes List: </h3>
<table class="table table-striped">
	<thead class="thead-dark">
		<tr>
			<th>Quote</th>
			<th>Author</th>
			<th>Template</th>
			<th>Action</th>
		</tr>
	</thead>

	<tbody>
		<?php 
		foreach($quotes as $quote){
			echo "<tr><td>".$quote->quote."</td>";
			echo "<td>".$quote->author."</td>";
			echo "<td>".$quote->template."</td>";
			echo "<td>";
				echo "<a href='".admin_url()."admin.php?page=preloader-quotes&action=edit&quote_id=".$quote->id."'>Edit | </a>";
				echo "<a href='".admin_url()."admin.php?page=preloader-quotes&action=delete&quote_id=".$quote->id."'>Delete</a>";
			echo "</td>";
			echo "</tr>";
		}
		?>
	</tbody>
</table>




