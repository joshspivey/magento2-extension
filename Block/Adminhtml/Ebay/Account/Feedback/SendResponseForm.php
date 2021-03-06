<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Account\Feedback;

class SendResponseForm extends \Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractForm
{
    protected function _prepareForm()
    {
        $feedback = $this->getHelper('Data\GlobalData')->getValue('feedback');

        $form = $this->_formFactory->create(
            ['data' => [
                'id' => 'send_response_form',
                'action' => 'javascript:void(0)',
                'method' => 'post',
            ]]
        );

        $form->addField(
            'feedback_id',
            'hidden',
            [
                'name' => 'feedback_id',
                'value' => $feedback->getId()
            ]
        );


        $fieldset = $form->addFieldset(
            'send_response_fieldset',
            []
        );

        $transaction = $feedback->getEbayTransactionId() == 0 ?
            $this->__('No ID For Auction') : $feedback->getEbayTransactionId();
        $url = $this->getUrl('*/*/goToOrder/', ['feedback_id' => $feedback->getId()]);

        $fieldset->addField(
            'transaction_id',
            self::CUSTOM_CONTAINER,
            [
                'label' => $this->__('Transaction ID'),
                'text' => <<<HTML
<a href="{$url}">{$transaction}</a>
HTML
            ]
        );

        $url = $this->getUrl('*/*/goToItem', array('feedback_id' => $feedback->getId()));

        $fieldset->addField(
            'item_id',
            self::CUSTOM_CONTAINER,
            [
                'label' => $this->__('Item ID'),
                'text' => <<<HTML
<a href="{$url}">{$feedback->getEbayItemId()}</a>
HTML
            ]
        );

        $fieldset->addField(
            'buyer_text',
            self::CUSTOM_CONTAINER,
            [
                'label' => $this->__('Buyer\'s Feedback'),
                'text' => <<<HTML
{$feedback->getData('buyer_feedback_text')}
HTML
            ]
        );

        $templates = $this->activeRecordFactory->getObject('Ebay\Feedback\Template')->getCollection()
            ->addFieldToFilter('main_table.account_id', $feedback->getData('account_id'));

        $fieldset->addField('feedback_template_type',
            self::SELECT,
            [
                'html_id' => 'feedback_template_type',
                'name' => 'feedback_template_type',
                'label' => $this->__('Response Type'),
                'values' => [
                    [
                        'value' => '',
                        'label' => '',
                        'attrs' => ['style' => 'display: none']
                    ],
                    [
                        'value' => 'custom',
                        'label' => $this->__('Custom')
                    ],
                    [
                        'value' => 'predefined',
                        'label' => $this->__('Predefined Template')
                    ]
                ],
                'value' => $templates->getSize() > 0 ? '' : 'custom',
                'required' => true,
                'class' => 'M2ePro-required-when-visible',
                'field_extra_attributes' => $templates->getSize() > 0  ? '' : 'style="display: none"'
            ]
        );

        $templatesValue = [[
            'value' => '',
            'label' => '',
            'attrs' => ['style' => 'display: none']
        ]];
        foreach ($templates as $template) {
            $text = $template->getData('body');

            if (strlen($text) > 40) {
                $text = substr($text, 0, 40) . '...';
            }

            $templatesValue[] = [
                'value' => $template->getData('body'),
                'label' =>  $text
            ];
        }

        $fieldset->addField('feedback_template',
            self::SELECT,
            [
                'html_id' => 'feedback_template',
                'container_id' => 'feedback_template_container',
                'name' => 'feedback_template',
                'label' => $this->__('Template'),
                'values' => $templatesValue,
                'required' => true,
                'class' => 'M2ePro-required-when-visible',
                'field_extra_attributes' => 'style="display: none"'
            ]
        );

        $fieldset->addField('feedback_text',
            'textarea',
            [
                'html_id' => 'feedback_text',
                'container_id' => 'feedback_text_container',
                'name' => 'feedback_text',
                'label' => $this->__('Message'),
                'required' => true,
                'class' => 'M2ePro-validate-feedback-response-max-length',
                'field_extra_attributes' => $templates->getSize() > 0  ? 'style="display: none"' : ''
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $this->js->add(<<<JS
    $('feedback_template_type').observe('change', function () {
    
        $('feedback_text_container').hide();
        $('feedback_template_container').hide();
    
        if ($('feedback_template_type').value == 'custom') {
            $('feedback_text_container').show();
            $('feedback_template_container').hide();
        } else if ($('feedback_template_type').value == 'predefined'){
            $('feedback_text_container').hide();
            $('feedback_template_container').show();
        }
    });
JS
        );

        return parent::_prepareForm();
    }
}