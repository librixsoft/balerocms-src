<?php

class virtual_page_Controller extends Controller {

    private Security $objSecurity;
    public virtual_page_Model $model;
    public virtual_page_View $view;
    private string $lang;

    public function __construct() {
        try {
            $this->objSecurity = new Security();
            $this->request = new RequestHelper($this->objSecurity);
            parent::__construct($this->request);

            $this->model = new virtual_page_Model();
            $this->view = new virtual_page_View();

            $this->lang = $this->model->getLang();
            $this->view->lang = $this->lang;
            $this->model->lang = $this->lang;
        } catch (Exception $e) {
            $this->view = new virtual_page_View();
        }

        $this->init();
    }

    #[Get(sr: '')]
    public function main() {
        try {
            if ($this->request->get('id')) {
                $id = $this->objSecurity->toInt($this->request->get('id'));
                $this->model->lang = $this->objSecurity->antiXSS($this->request->get('sr'));
                $query_content = $this->model->get_virtual_page_by_id($id);
                $this->view->rows = $this->model->rows;
                $this->view->content .= "<div id=\"vp-content\">" .
                    ($this->view->print_virtual_page($query_content)) .
                    "</div>";
            } else {
                throw new Exception();
            }
        } catch (Exception $e) {
            $this->view->page = _NOT_FOUND;
            $msgbox = new MsgBox(_VP, _VP_DONT_EXIST);
            $this->view->content .= $msgbox->Show();
        }

        $this->view->RenderView();
    }
}
