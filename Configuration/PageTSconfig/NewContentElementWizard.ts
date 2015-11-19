mod {
	wizards {
		newContentElement {
			wizardItems {
				common {
					elements {
						textmedia {
							icon = gfx/c_wiz/text_image_right.gif
							title = LLL:EXT:content_rendering_core/Resources/Private/Language/locallang_db_new_content_el.xlf:textmedia.title
							description = LLL:EXT:content_rendering_core/Resources/Private/Language/locallang_db_new_content_el.xlf:textmedia.description
							tt_content_defValues {
								CType = textmedia
								imageorient = 17
							}
						}
					}
					show := addToList(textmedia)
				}
			}
		}
	}
}