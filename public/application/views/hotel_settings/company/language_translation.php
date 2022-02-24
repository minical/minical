<div class="app-page-title">
 <div class="page-title-wrapper">
    <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-pen text-success"></i>
            </div>
            <h3><?php echo l('Language Translation');?></h3>
            
        </div>
 </div>   
</div>
<!--Show success or error message-->
<?php 
    if($this->session->flashdata('success_message'))
    {
?>
        <div class="alert alert-success">
            <?php echo $this->session->flashdata('success_message'); ?>.
        </div>
<?php
    }
    else if($this->session->flashdata('error_message'))
    {
?>
        <div class="alert alert-danger">
            <?php echo $this->session->flashdata('error_message'); ?>.
        </div>
<?php
    }
?>
<!-- Call helper to get languages -->
<?php $languages = get_languages(); 
$lang_id = $this->uri->segment(3);
//print_r($languages);die();
// if($this->is_super_admin == 1){
?>
<div class="main-card mb-3 card">
<div class="card-body">
<?php
 if($this->is_super_admin != 1){ ?>
 <div style="text-align: center;"> <p>
    <b> The language translation editor is only accessed by the admin.</b>
 </p></div>
 <?php }else {
?>    
<!--Show success or error message-->
<div class="add_language_button" style="margin-bottom:15px;">
<button data-toggle="modal" data-target="#add_language" class="btn btn-primary"><?php echo l('Add Language');?> </button>
</div>

<form id="language_translation" method="post">
        <div class=" form-horizontal">
            <div class="form-group">
                        
                        <div class="col-sm-3">
                            <!-- <select class="form-control" name="language_id" id="language_id" onchange="changeTranslationLanguage(this.value)" required >
                                <option value="">-- Select Language --</option>
                                <?php $is_enable = ''; if(!empty($languages)){ foreach ($languages as $value) { ?>
                                    <option value="<?php echo $value['id']; ?>" language_status="<?php echo $value['is_enable']; ?>" <?php if($language_id == $value['id']) { $is_enable = $value['is_enable']; ?>selected="selected"<?php } ?> ><?php echo $value['language_name']; ?></option>
                               <?php }} ?>
                            </select> -->

                            <select class="form-control" name="language_id" id="language_id" onchange="changeTranslationLanguage(this.value)" required >
                                <option value="0"><?php echo l('Select Language');?></option>
                                <?php $is_enable = ''; if(!empty($languages)){ foreach ($languages as $value) {  if ($value['language_name'] == 'English') {continue;}?>
                                    <option value="<?php echo $value['id']; ?>" language_status="<?php echo $value['is_enable']; ?>" <?php if($language_id == $value['id']) { $is_enable = $value['is_enable']; ?>selected="selected"<?php } ?> ><?php echo $value['language_name']; ?></option>
                               <?php }} ?>
                            </select>
                            <input type="hidden" name="language_name" id="languagename" value="">
                        </div>
                        
                        <?php if(isset($translationRecords) && $translationRecords): ?>
                        <div class="col-sm-4">
                            <label for="language_enable" class="col-sm-4 control-label" style="font-size: 19px;"><?php echo l('Enable');?></label>
                            <div class="col-sm-8">
                                <select class="form-control " name="language_enable" id="language_enable" onchange="changeLanguageStatus(this.value)" >
                                    <option value="1" language_id="<?php echo $language_id; ?>" <?php if($is_enable==1) { ?>selected="selected"<?php } ?> >Yes</option>
                                    <option value="0" language_id="<?php echo $language_id; ?>" <?php if($is_enable==0) { ?>selected="selected"<?php } ?> >No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4" style="float: right;text-align: right;">
                            <input type="checkbox" class="non_translated_phrase" name="non_translated_phrase" <?php echo $non_translated_phrase ? 'checked="checked"' : ''; ?>>
                            <label for="name" class="control-label"><?php echo l('Show non-translated phrases only');?></label>
                        </div>
                    <?php endif; ?>
                        <input type="hidden" name="non_translated_key" class="non_translated_key" value="<?php echo isset($_GET['non_translated_phrase']) && $_GET['non_translated_phrase'] == 1 ? 1 : 0; ?>">
                </div>
            <br/><br/>
                <?php if(isset($translationRecords) && $translationRecords): ?>
                <div class="">
                    <table class="table language-translation table-bordered">
                        <thead>
                            <tr>
                                <th style="font-size:20px;text-align: left;width: 30%;"><?php echo l('Phrase');?></th>
                                <?php if($this->is_super_admin): ?>
                                <th style="font-size:20px;text-align: left;width: 30%;"><?php echo l('Default');?></th>
                                <?php endif; ?>
                                <th style="font-size:20px; width: 40%;">
                                    <span id="languagelabel">English</span> <?php echo l('Translation');?>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="input_fields_wrap">
                            <?php if(!empty($translationRecords)) { 
                                foreach ($translationRecords as $key => $value) { ?>
                                <tr>
                                    <td style="text-align: left;">
                                        <label for="name" class="control-label" style="text-align: left;"><?php echo isset($value['phrase_keyword']) && $value['phrase_keyword'] ? $value['phrase_keyword'] : ""; ?></label>
                                        <div class="success_msg_<?php echo isset($value['tid']) && $value['tid'] ? $value['tid'] : $value['pid']; ?>"></div>
                                    </td>
                                    <?php if($this->is_super_admin): ?>
                                    <td style="text-align: left;">
                                        <label for="name" class="control-label" style="text-align: left;"><?php echo isset($value['default_key']) && $value['default_key'] ? $value['default_key'] : $value['phrase_keyword']; ?></label>
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <input type="text" class="form-control" onblur="update_translation_phrase('<?php echo $value['tid']; ?>', '<?php echo $value['pid']; ?>', this.value)" name="selected_language_phrase1[]" id="selected_language_phrase1" value="<?php echo isset($value['phrase']) && $value['phrase'] ? $value['phrase'] : ''; ?>" <?php echo (($lang_id == 1 || $lang_id == '') && !$this->is_super_admin) ? 'disabled' : ''; ?>>
                                    </td>
                                </tr>
                            <?php }} ?>
                        </tbody>
                    </table>

                    <input type="hidden" name="default_language_id" value="<?php if(!empty($defaultLang)) echo $defaultLang['id']; ?>">
                    <input type="hidden" class="language-id" name="language_id" value="<?php echo $language_id; ?>">
                    <input type="hidden" class="language-status" name="language_status" value="<?php echo $language_status; ?>">

                </div>
            <?php endif; ?>
            <!-- <div class="form-group input_fields_wrap"></div> -->
            
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-5"></div>
                    <div class="col-md-3">
                        <div class="form-group saveBT" style="display: none;float: right;">
                            <button type="submit" onclick="if(document.getElementsByClassName('checkFields').length === 0){ alert('Please add at least one field'); return false; }" class="btn btn-primary" name="languagetranslation" id="languagetranslation">
                                Save
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">

                    </div>
                </div>
            </div>

        </div>
</form>
<?php } ?>
</div>
</div>
<div class="modal fade" id="add_language">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo l('Add New Language');?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="add_language">
                    <div class="form-group">
                        <label for="lang_name" class="col-form-label"><?php echo l('Language Name');?></label>
                        <input type="text" class="form-control" id="lang_name" name="lang_name" value="">
                    </div>
                    <div class="form-group right mt-3">
                        <button type="button" id="addlanguage" class="btn btn-primary"><?php echo l('Add');?></button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo l('Close');?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
