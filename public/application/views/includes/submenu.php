<?php if(!empty($menu_items)) : ?>
    <div id="submenu" class="<?=$this->session->userdata('user_role') == "is_housekeeping" ? "hidden" : "";?>">
        <div class="max-width container hidden-print">
            <ul>
                <?php $selected_submenu = isset($selected_submenu) && $selected_submenu ? str_replace(" ", "_", strtolower($selected_submenu)) : ""; ?>

                <?php foreach ($menu_items as $menu_item):
                    if(
                        $menu_item['name'] == 'statements' ||
                        $menu_item['name'] == 'inventory' ||
                        $menu_item['name'] == 'date_manager'
                    ){
                        continue;
                    } ?>
                    <li>
                        <a href="<?php if($menu_item['name'] == 'statements' || $menu_item['name'] == 'booking_history') { echo $submenu_parent_url.$menu_item['link'].'/'.($this->uri->segment(3)); } else { echo $submenu_parent_url.$menu_item['link']; } ?>"
                            <?php if (isset($selected_submenu)) : ?>
                            <?php   if (l($selected_submenu) == l($menu_item['name'])) echo 'id="selected_submenu"'; ?>
                            <?php endif; ?>>
                                <?php
                                if(l($menu_item['name'])){
                                    echo l($menu_item['name']);
                                }
                                else{
                                    echo l($menu_item['name']);
                                }
                                ?>
                            </a>
                    </li>
                <?php endforeach; ?>

                <?php 
                
                    $module_menus = $this->module_menus;
                    
                    foreach ($module_menus as $key => $mod_menu) {
                        if($key == 'customer_statements'){
                            foreach($mod_menu as $m_menu){ 
                                if($m_menu['location'] == 'SECONDARY'){ ?>
                                    <li >
                                        <a href="<?php echo base_url().$m_menu['link'];?>"
                                        <?php if (isset($selected_submenu)) : ?>
                                        <?php if (l($selected_submenu, true) == l($m_menu['label'], true)) echo 'id="selected_submenu"'; ?>
                                        <?php endif; ?>
                                        >
                                            <?php echo l($m_menu["label"],true);?>
                                        </a>
                                    </li>
                                <?php   
                                } 
                            }
                        }
                    }
                ?>

            </ul>
        </div>
    </div>
<?php endif; ?>
