<ul>
      <?php 
	$k = 0;
	
	foreach($fileinfo as $index=>$value):  ?>
	

	
	
	
	
	
	  <li><label><input type="checkbox" value="<?php echo $value['name'] ?>" name="imports[]" class="role"> &nbsp; <?php echo $value['name'] ?></label></li>
	
	
     
      <?php
	  
	  $k++;
	  endforeach; ?>
	  
	  </ul>
	  