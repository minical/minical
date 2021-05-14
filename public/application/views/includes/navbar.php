  <nav class="navbar navbar-expand navbar-light navbar-bg">
        <a class="sidebar-toggle d-flex">
            <i class="hamburger align-self-center"></i>
        </a>

        <form class="d-none d-sm-inline-block">
            <div class="input-group input-group-navbar">
                <input type="text" class="form-control" placeholder="Search…" aria-label="Search">
                <button class="btn" type="button">
                    <i class="align-middle" data-feather="search"></i>
                </button>
            </div>
        </form>

        <div class="navbar-collapse collapse">
            <ul class="navbar-nav navbar-align">

                <li>
                    <?php $languages = get_enabled_languages();
                    $current_language = $this->session->userdata('language'); ?>
                    <li class='current_language <?=$this->session->userdata('user_role') == "is_housekeeping" ? "hidden" : "";?>'
                        data-toggle="popover" 
                        data-placement="bottom" 
                        data-trigger="manual" 
                        data-animation="true" 
                        data-content="Click here to change language.">
                        
                        
                        <a class="nav-flag dropdown-toggle" href="#" id="languageSelection" data-toggle="dropdown" aria-expanded="true">
                            <img src="<?php echo base_url().'images/language_flags/'.$current_language.'.png'; ?>">
                            <span class="caret"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="languageSelection">
                            <?php if(!empty($languages)):
                                foreach($languages as $key => $value): ?>   
                                    <a class="dropdown-item" href="#">
                                        <img src="<?php echo base_url().'images/language_flags/'.$value['flag'].'.png'; ?>" alt="<?php echo $value['language_name']; ?>" width="20" class="align-middle mr-1">
                                        <span class="align-middle"><?php echo $value['language_name']; ?></span>
                                    </a>
                                <?php endforeach;
                            endif; ?>   
                        </div>
                    </li>
                    
                </a>
                
                <li>

                    <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href='#' id="myAccountMenu" data-toggle="dropdown" aria-expanded="true">
                        <span id="user_email" class="text-dark"><?php echo $this->session->userdata('email'); ?></span>
                        <input id='user_id' value='<?php echo $this->user_id; ?>' style='display:none;' />
                        <span class="caret"></span>
                    </a>
                    
                    
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="pages-profile.html"><i class="align-middle mr-1" data-feather="user"></i><?php echo l('Profile', true);?></a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#"><?php echo l('Log out', ture);?></a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>