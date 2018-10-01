<script type="text/javascript">
  function delete_row(id) {
    console.log('hi');
    if(confirm("Are you sure you want to delete this Record?")) {

      // Action is the key to mapping out an ajax request
      let data = {action: 'hvac_contact_delete_entry', delete_row: 'delete_row', row_id:id};

        jQuery.post(ajaxurl, data, function(r){
          jQuery(r).remove();
        })
    }
  }	
</script>

<form method="post" action="">
  <table style="width: 100%;" id="customers">
    <tbody>
      <tr>
        <th style="text-align: left;">Name</th>
        <th style="text-align: left;">Email</th>
        <th style="text-align: left;">Phone</th>
        <th style="text-align: left;">Message</th>
        <th style="text-align: left;">Time</th>
      </tr>

      <?php 
      foreach ($retrieve_data as $v) {
        echo '<tr id="delete_button' . $v->id . '">
          <td>' . $v->name . '</td>
          <td>' . $v->email . '</td>
          <td>' . $v->phone . '</td>
          <td>' . $v->message . '</td>
          <td>' . $v->time . '</td>
          <td style="text-align: center; ">
              <a href="#" onclick="delete_row(' . $v->id  . ');">Delete</a>
          </td>
        </tr>';
      }
      ?>
      
    </tbody>
  </table>
</form>