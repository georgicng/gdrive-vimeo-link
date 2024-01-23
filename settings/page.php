<style>
  .container{
      width: 800px;
      margin: 0 auto;
  }
  ul.tabs{
      margin: 0px;
      padding: 0px;
      list-style: none;
  }
  ul.tabs li{
      background: none;
      color: #222;
      display: inline-block;
      padding: 10px 15px;
      cursor: pointer;
  }
  ul.tabs li.current{
      background: #ededed;
      color: #222;
  }
  .tab-content{
      display: none;
      background: #ededed;
      padding: 15px;
  }
  .tab-content.current{
      display: inherit;
  }
</style>
<div class="container">
    <nav class="tabs">
        <a 
          class="tab-link <?php echo (empty($_GET['tab']) || $_GET['tab'] == 'tab-1') ? 'current' : '' ?>" 
          href="<?php echo admin_url('options-general.php?page=gai_gdrive_token&tab=tab-1') ?>">
          <?php echo __('Google Drive', 'gdrive-vimeo-link') ?>
        </a>
        <a 
          class="tab-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'tab-2')? 'current' : '' ?>"
          href="<?php echo admin_url('options-general.php?page=gai_gdrive_token&tab=tab-2') ?>">
          <?php echo __('Vimeo', 'gdrive-vimeo-link') ?>
        </a>
    </nav>
    
    <?php if (empty($_GET['tab']) || $_GET['tab'] == 'tab-1') { ?> 
      <div id="tab-1" class="tab-content current">        
        <?php if ($alert) { ?>
          <div><?php echo __('Something went wrong, Please try again', 'gdrive-vimeo-link') ?></div>
        <?php } ?>
        <h2><?php echo __('Google Drive Authorisation', 'gdrive-vimeo-link') ?></h2>
        <p><?php echo $message; ?></p>
        <?php if (isset($auth_url)) { ?>
            <div>
                <a href="<?php echo $auth_url ?>" class="button button-primary button-large">
                <?php echo __('Click to authorise App', 'gdrive-vimeo-link') ?>
                </a>
            </div>
        <?php } ?>
      </div>
    <?php } ?>

    <?php if (isset($_GET['tab']) && $_GET['tab'] == 'tab-2') { ?> 
      <div id="tab-2" class="tab-content current">
        <?php if ($alert) { ?>
          <div> <?php echo __("You're required to set the API credentials", 'gdrive-vimeo-link') ?> <a href="customize.php"><?php echo __("here", 'gdrive-vimeo-link') ?></a> </div>
        <?php } ?>
        <h2><?php echo __('Vimeo Authorisation', 'gdrive-vimeo-link') ?></h2>
        <p><?php echo $message; ?></p>
        <?php if (isset($auth_url)) { ?>
          <div>
          <a href="<?php echo $auth_url ?>"><?php echo __('Authorize App', 'gdrive-vimeo-link') ?></a>
          </div>
        <?php } ?>
      </div>
    <?php } ?>

</div><!-- container -->
