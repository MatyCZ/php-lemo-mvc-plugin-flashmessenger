<?php

namespace Lemo\Mvc\Controller\Plugin;

use Laminas\Form\FormInterface;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger as FlashMessengerPlugin;
use Laminas\View\Renderer\PhpRenderer;
use Lemo\Mvc\Exception;

use function implode;
use function in_array;
use function strtolower;
use function sort;
use function sprintf;
use function ucfirst;

class FlashMessenger extends FlashMessengerPlugin
{
    /**
     * Warning messages namespace
     */
    public const NAMESPACE_WARNING = 'warning';

    /**
     * Messages types
     */
    public const TYPE_ERROR       = 'error';
    public const TYPE_ERROR_FORM  = 'error';
    public const TYPE_INFORMATION = 'information';
    public const TYPE_SUCCESS     = 'success';
    public const TYPE_WARNING     = 'warning';

    /**
     * List of allowed message types
     */
    protected array $allowedTypes = [
        self::TYPE_ERROR,
        self::TYPE_ERROR_FORM,
        self::TYPE_INFORMATION,
        self::TYPE_SUCCESS,
        self::TYPE_WARNING
    ];

    /**
     * Add new error message
     *
     * @param string $message
     */
    public function addErrorMessage($message, ?string $title = null): self
    {
        if (null === $title) {
            $title = 'Error';
        }

        parent::addErrorMessage(
            [
                'title' => $title,
                'message' => $message
            ]
        );

        return $this;
    }

    /**
     * Add errors notices from form
     */
    public function addFormErrorMessages(FormInterface $form): self
    {
        $formError = [];
        $messages = $form->getInputFilter()->getMessages();

        // Grab errors from fieldsets
        foreach ($form->getFieldsets() as $fieldset) {
            $elements = $fieldset->getElements();
            foreach ($fieldset->getInputFilter()->getMessages() as $errors) {
                foreach ($errors as $element => $fieldsetMessages) {
                    if (isset($elements[$element])) {
                        foreach($fieldsetMessages as $message) {
                            $formError[$message][] = $elements[$element]->getLabel();
                        }
                    }
                }
            }

            unset($messages[$fieldset->getName()]);
        }

        // Grab errors from form
        $elements = $form->getElements();
        foreach ($messages as $element => $errors) {
            foreach ($errors as $message) {
                if (isset($elements[$element])) {
                    $formError[$message][] = $this->getController()
                        ->getServiceLocator()
                        ->get(PhpRenderer::class)
                        ->translate($elements[$element]->getLabel());
                }
            }
        }

        // Add error notices
        foreach ($formError as $message => $elements) {
            sort($elements);

            parent::addErrorMessage(
                [
                    'title' => $message,
                    'message' => implode(', ', $elements)
                ]
            );
        }

        return $this;
    }

    /**
     * Add new information message
     *
     * @param string $message
     */
    public function addInfoMessage($message, ?string $title = null): self
    {
        if (null === $title) {
            $title = 'Information';
        }

        parent::addInfoMessage(
            [
                'title' => $title,
                'message' => $message
            ]
        );

        return $this;
    }

    /**
     * Add new success message
     *
     * @param string $message
     */
    public function addSuccessMessage($message, ?string $title = null)
    {
        if (null === $title) {
            $title = 'Success';
        }

        parent::addSuccessMessage(
            [
                'title' => $title,
                'message' => $message
            ]
        );

        return $this;
    }

    /**
     * Add new warning message
     *
     * @param string $message
     */
    public function addWarningMessage($message, ?string $title = null): self
    {
        if (null === $title) {
            $title = 'Warning';
        }

        $namespace = $this->getNamespace();
        $this->setNamespace(self::NAMESPACE_WARNING);
        $this->setNamespace($namespace);

        parent::addMessage(
            [
                'title' => $title,
                'message' => $message
            ]
        );

        return $this;
    }

    /**
     * Has warning messages?
     */
    public function hasWarningMessages(): bool
    {
        $namespace = $this->getNamespace();
        $this->setNamespace(self::NAMESPACE_WARNING);
        $hasMessages = $this->hasMessages();
        $this->setNamespace($namespace);

        return $hasMessages;
    }

    /**
     * Get warning messages
     */
    public function getWarningMessages(): array
    {
        $namespace = $this->getNamespace();
        $this->setNamespace(self::NAMESPACE_WARNING);
        $messages = $this->getMessages();
        $this->setNamespace($namespace);

        return $messages;
    }

    /**
     * Add new message
     *
     * @param  string      $message
     * @param  string|null $title
     * @param  string      $type
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function addMessage($message, ?string $title = null, $type = self::TYPE_SUCCESS): self
    {
        $type = strtolower($type);

        if (!in_array($type, $this->allowedTypes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                "Invalid message type given. Only types '%s' are supported.",
                implode(', ', $this->allowedTypes)
            ));
        }

        // Set namespace to given type
        $this->setNamespace($type);

        // Create title
        if (null === $title) {
            $title = ucfirst($type);
        }

        parent::addMessage(
            [
                'title' => $title,
                'message' => (string) $message,
            ]
        );

        return $this;
    }
}
