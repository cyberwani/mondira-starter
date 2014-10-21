<?php
/*
* 
* This Class is to generate shortcode 
* 
* @since version 1.0
* @last modified 19 Oct, 2014
* @author Jewel Ahmed<tojibon@gmail.com>
* @author url http://www.codeatomic.com 
* 
*/ 
if ( !class_exists( 'MondiraThemeShortcodesGenerator' ) ) {
	class MondiraThemeShortcodesGenerator {
        var $shortcodes = array();
        var $html;
        
        public function init() {
            add_action( 'media_buttons', array( &$this, 'mondira_editor_buttons' ), 100 );            
            add_action( 'admin_footer', array( &$this, 'generated_shortcodes_html' ) );            
        }
		
		public function mondira_editor_buttons() {
			if( is_admin() ) { 
				echo "<a class='button mondira-shortcode-generator' href='#mondira-shortcode-generator'>Theme Shortcodes</a>";
			}
		}
		
		public function mondira_addshortcode( $key, $attr ) {
			$this->shortcodes[$key]=$attr;
		}
		
		public function mondira_shortcode_fields( $name, $attr_option, $shortcode, $unique_option_id = '' ) {
			$shortcode_field_html = $desc = $class = $postfix = $suffix = $value = '';
			
			if ( empty( $unique_option_id ) ) {
				$unique_option_id = time();
			}
			
			if( !empty( $attr_option['desc'] ) ) {
				$desc = '<p class="description">'.$attr_option['desc'].'</p>';
			} else if( !empty( $attr_option['description'] ) ) {	//Adding support for Visual Composer default attributes
				$desc = '<p class="description">'.$attr_option['description'].'</p>';
			} 
			
			if( !empty( $attr_option['class'] ) ) {
				$class = $attr_option['class'];
			}
			if( !empty( $attr_option['postfix'] ) ) {
				$postfix = '<span class="postfix">' . $attr_option['postfix'] . '</span>';
			}
			if( !empty( $attr_option['suffix'] ) ) {
				$suffix = '<span class="suffix">' . $attr_option['suffix'] . '</span>';
			}
			if( !empty( $attr_option['value'] ) ) {
				$value = $attr_option['value'];
			} 
			
			$label = '';
			if ( !empty( $attr_option['title'] ) ) {
				$label = $attr_option['title'];
			} else if ( !empty( $attr_option['heading'] ) ) {
				$label = $attr_option['heading'];	//Adding support for Visual Composer default attributes
			}
			
			//Shortcode options dependency control
			$dependency_element = $dependency_is_empty = $dependency_not_empty = $dependency_values = '';
			if ( !empty( $attr_option['dependency'] ) && is_array( $attr_option['dependency'] ) ) {
				$display = 'none';
				
				$dependency = $attr_option['dependency'];
				if ( !empty( $dependency['element'] ) ) {
					$dependency_element = $dependency['element'];
				} else {
					$dependency_element = '';
				}
				
				if ( !empty( $dependency['is_empty'] ) && $dependency['is_empty'] ) {
					$dependency_is_empty = 'true';
				} else {
					$dependency_is_empty = 'false';
				}
				
				if ( !empty( $dependency['not_empty'] ) && $dependency['not_empty'] ) {
					$dependency_not_empty = 'true';
				} else {
					$dependency_not_empty = 'false';
				}
				
				if ( !empty( $dependency['values'] ) && $dependency['values'] ) {
					$dependency_values = implode( '|', $dependency['values'] );
				} else {
					$dependency_values = '';
				}
				
			} else {
				$display = 'block';
			}
			
			
			switch( $attr_option['type'] ) {
				case 'radio':
					$shortcode_field_html .= '
					<div class="atomic-shortcode-option" id="atomic-shortcode-option-'.$unique_option_id.'" data-shortcode="'.$shortcode.'" data-dependency_element="'.$dependency_element.'" data-dependency_is_empty="'.$dependency_is_empty.'" data-dependency_not_empty="'.$dependency_not_empty.'" data-dependency_values="'.$dependency_values.'" data-display="'.$display.'">
						<div class="label"><strong>'.$label.': </strong></div><div class="content">';
						foreach( $attr_option['opt'] as $val => $title ){
							(isset($attr_option['def']) && !empty($attr_option['def'])) ? $def = $attr_option['def'] : $def = '';
							 $shortcode_field_html .= '
								<label for="shortcode-option-'.$shortcode.'-'.$name.'-'.$val.'">'.$title.'</label>
								<input class="attr" type="radio" data-attrname="'.$name.'" name="'.$shortcode.'-'.$name.'" value="'.$val.'" id="shortcode-option-'.$shortcode.'-'.$name.'-'.$val.'"'. ( $val == $def ? ' checked="checked"':'').'>';
						}
						$shortcode_field_html .=  $postfix . ' ' . $desc . '</div>
					</div>';
					break;
					
				case 'checkbox':
					$shortcode_field_html .= '
					<div class="atomic-shortcode-option" id="atomic-shortcode-option-'.$unique_option_id.'" data-shortcode="'.$shortcode.'" data-dependency_element="'.$dependency_element.'" data-dependency_is_empty="'.$dependency_is_empty.'" data-dependency_not_empty="'.$dependency_not_empty.'" data-dependency_values="'.$dependency_values.'" data-display="'.$display.'">
						<div class="label"><label for="' . $name . '"><strong>' . $label . ': </strong></label></div>    
						<div class="content container-checkbox">' . $suffix . '<input type="checkbox" data-attrname="'.$name.'" class="' . $name . '" id="' . $name . '" />' . $postfix . ' ' . $desc. '</div>
					</div>';
					break;	
				
				case 'select':
				case 'dropdown': //Adding support for Visual Composer default attributes
					$shortcode_field_html .= '
					<div class="atomic-shortcode-option" id="atomic-shortcode-option-'.$unique_option_id.'" data-shortcode="'.$shortcode.'" data-dependency_element="'.$dependency_element.'" data-dependency_is_empty="'.$dependency_is_empty.'" data-dependency_not_empty="'.$dependency_not_empty.'" data-dependency_values="'.$dependency_values.'" data-display="'.$display.'">
						<div class="label"><label for="'.$name.'"><strong>'.$label.': </strong></label></div>
						<div class="content container-select">' . $suffix . '<select data-attrname="'.$name.'" id="'.$name.'">';
						$values = $attr_option['values'];
						foreach( $values as $key=>$value ){
							$shortcode_field_html .= '<option value="'.$key.'">'.$value.'</option>';
						}
						$shortcode_field_html .= '</select>' . $postfix . ' '  . $desc . '</div>
					</div>';
					break;
				
				case 'multi-select':
					$shortcode_field_html .= '
					<div class="atomic-shortcode-option" id="atomic-shortcode-option-'.$unique_option_id.'" data-shortcode="'.$shortcode.'" data-dependency_element="'.$dependency_element.'" data-dependency_is_empty="'.$dependency_is_empty.'" data-dependency_not_empty="'.$dependency_not_empty.'" data-dependency_values="'.$dependency_values.'" data-display="'.$display.'">
						<div class="label"><label for="'.$name.'"><strong>'.$label.': </strong></label></div>
						<div class="content container-multi-select">' . $suffix . '<select data-attrname="'.$name.'" multiple="multiple" id="'.$name.'">';
						$values = $attr_option['values'];
						foreach( $values as $k => $v ){
							$shortcode_field_html .= '<option value="'.$k.'">'.$v.'</option>';
						}
						$shortcode_field_html .= '</select>' . $postfix . ' '  . $desc . '</div>
					</div>';
					break;
					
				case 'textarea':
					$shortcode_field_html .= '
					<div class="atomic-shortcode-option" id="atomic-shortcode-option-'.$unique_option_id.'" data-shortcode="'.$shortcode.'" data-dependency_element="'.$dependency_element.'" data-dependency_is_empty="'.$dependency_is_empty.'" data-dependency_not_empty="'.$dependency_not_empty.'" data-dependency_values="'.$dependency_values.'" data-display="'.$display.'">
						<div class="label"><label for="shortcode-option-'.$name.'"><strong>'.$label.': </strong></label></div>
						<div class="content container-textarea">' . $suffix . '<textarea data-attrname="'.$name.'"></textarea> ' . $postfix . ' '  . $desc . '</div>
					</div>';
					break;
						
				case 'color':
				case 'colorpicker': //Adding support for Visual Composer default attributes
					$shortcode_field_html .= '
					<div class="atomic-shortcode-option" id="atomic-shortcode-option-'.$unique_option_id.'" data-shortcode="'.$shortcode.'" data-dependency_element="'.$dependency_element.'" data-dependency_is_empty="'.$dependency_is_empty.'" data-dependency_not_empty="'.$dependency_not_empty.'" data-dependency_values="'.$dependency_values.'" data-display="'.$display.'">
						<div class="label"><label for="shortcode-option-'.$name.'"><strong>'.$label.': </strong></label></div>
						<div class="content container-color">' . $suffix . '<input class="attr '.$class.'" type="text" data-attrname="'.$name.'" value="" />' . $postfix . ' '  . $desc . '</div>
					</div>';
					break;
				
				case 'number':
					$shortcode_field_html .= '
					<div class="atomic-shortcode-option" id="atomic-shortcode-option-'.$unique_option_id.'" data-shortcode="'.$shortcode.'" data-dependency_element="'.$dependency_element.'" data-dependency_is_empty="'.$dependency_is_empty.'" data-dependency_not_empty="'.$dependency_not_empty.'" data-dependency_values="'.$dependency_values.'" data-display="'.$display.'">
						<div class="label"><label for="shortcode-option-'.$name.'"><strong>'.$label.': </strong></label></div>
						<div class="content container-number">' . $suffix . '<input class="attr '.$class.'" type="number" data-attrname="'.$name.'" value="'.$value.'" />' . $postfix . ' ' . $desc . '</div>
					</div>';
					break;
				
				case 'attach_image':
					$shortcode_field_html .= '
					<div class="atomic-shortcode-option" id="atomic-shortcode-option-'.$unique_option_id.'" data-shortcode="'.$shortcode.'" data-dependency_element="'.$dependency_element.'" data-dependency_is_empty="'.$dependency_is_empty.'" data-dependency_not_empty="'.$dependency_not_empty.'" data-dependency_values="'.$dependency_values.'" data-display="'.$display.'">
					<div class="label"><label for="shortcode-option-'.$name.'"><strong>'.$label.': </strong></label></div>
						<div class="content container-image">' . $suffix . '<input class="attr '.$class.'" type="text" id="'.$name.'" data-attrname="'.$name.'" value="'.$value.'" />' . $postfix;
					$shortcode_field_html .= $desc;
					$shortcode_field_html.= '
						<br class="clearfix" />
						<div class="mondira-media-upload-button">
							<a class="upload_image_button_latest button theme-upload-button" data-target="'.$name.'" id="'.$name.'_thickbox" href="media-upload.php?post_id=0&target='.$name.'&mondira_image_upload=1&type=image&TB_iframe=1&width=640&height=644">Upload/Choose One</a><br />';
					
					$shortcode_field_html.= '<div id="'.$name.'_preview" class="mondira-media-preview"></div>';
					$shortcode_field_html.= '</div>';
					$shortcode_field_html.= '</div>';
					$shortcode_field_html.= '</div>';
					break;
				
				case 'attach_file':
					$shortcode_field_html .= '
					<div class="atomic-shortcode-option" id="atomic-shortcode-option-'.$unique_option_id.'" data-shortcode="'.$shortcode.'" data-dependency_element="'.$dependency_element.'" data-dependency_is_empty="'.$dependency_is_empty.'" data-dependency_not_empty="'.$dependency_not_empty.'" data-dependency_values="'.$dependency_values.'" data-display="'.$display.'">
					<div class="label"><label for="shortcode-option-'.$name.'"><strong>'.$label.': </strong></label></div>
						<div class="content container-image">' . $suffix . '<input class="attr '.$class.'" type="text" id="'.$name.'" data-attrname="'.$name.'" value="'.$value.'" />' . $postfix;
					$shortcode_field_html .= $desc;
					$shortcode_field_html.= '
						<br class="clearfix" />
						<div class="mondira-media-upload-button">
							<a class="upload_image_button_latest button theme-upload-button" data-target="'.$name.'" id="'.$name.'_thickbox" href="media-upload.php?post_id=0&target='.$name.'&mondira_image_upload=1&type=file&TB_iframe=1&width=640&height=644">Upload/Choose One</a><br />';
					$shortcode_field_html.= '</div>';
					$shortcode_field_html.= '</div>';
					$shortcode_field_html.= '</div>';
					break;
				
				case 'text':
				case 'textfield': //Adding support for Visual Composer default attributes
				default:
					$shortcode_field_html .= '
					<div class="atomic-shortcode-option" id="atomic-shortcode-option-'.$unique_option_id.'" data-shortcode="'.$shortcode.'" data-dependency_element="'.$dependency_element.'" data-dependency_is_empty="'.$dependency_is_empty.'" data-dependency_not_empty="'.$dependency_not_empty.'" data-dependency_values="'.$dependency_values.'" data-display="'.$display.'">
						<div class="label"><label for="shortcode-option-'.$name.'"><strong>'.$label.': </strong></label></div>
						<div class="content container-text">' . $suffix . '<input class="attr '.$class.'" type="text" data-attrname="'.$name.'" value="" />' . $postfix . ' '  . $desc . '</div>
					</div>';
					break;
			}
			
			//$shortcode_field_html .= '<div class="clear"></div>';
			
			return $shortcode_field_html;
		}


			
		public function generated_shortcodes_html()	{
			if ( empty( $this->shortcodes ) && is_array( $this->shortcodes ) ) {
				return '';
			}		
			$content_html = $option_html = '';
			ob_start();			
			if ( !empty( $this->shortcodes ) && is_array( $this->shortcodes ) ) {
				$i = 1;
				foreach( $this->shortcodes as $shortcode => $options ) {
					$title = '';
					if ( !empty( $options['title'] ) ) {
						$title = $options['title'];
					} else if ( !empty( $options['heading'] ) ) {
						$title = $options['heading'];	//Adding support for Visual Composer default attributes
					}
					if(strpos($shortcode,'header') !== false) {
						$option_html .= '<optgroup label="'.$title.'">';
					} else {
						$option_html .= '<option value="'.$shortcode.'">'.$title.'</option>';
						
						$content_html .= '<div class="shortcode-options" id="options-'.$shortcode.'" data-name="'.$shortcode.'" data-type="'.$options['type'].'">';
						if( !empty($options['attr']) ) {
							foreach( $options['attr'] as $name => $attr_option ) {
								$content_html .= $this->mondira_shortcode_fields( $name, $attr_option, $shortcode, $i );
								$i++;
							}
						}	
						$content_html .= '</div>'; 
					}
				} 
			}
			?>			 
			<div id="mondira-shortcode-heading">
				<div id="mondira-shortcode-generator" class="mfp-hide mfp-with-anim">
					<div class="shortcode-content">
						<div id="mondira-shortcode-header">
							<div class="label"><strong>Theme Shortcodes</strong></div>
							<div class="content">
								<select class="chosen" id="mondira-shortcodes" data-placeholder="Choose a shortcode">
									<option></option>
									<?php echo $option_html; ?>
								</select>
							</div>
						</div>
						<?php echo $content_html; ?>
					</div>				
					<code class="shortcode_code">
						<span id="shortcode-opening-tag" style=""></span>
						<span id="shortcode-inner-content"></span>
						<span id="shortcode-closing-tag" style=""></span>
					</code>
					<a class="btn" id="mondira-insert-shortcode">Insert Shortcode</a>
				</div>
			</div>
		<?php 
			$output = ob_get_clean();
			echo $output;
		}         
    }
}
