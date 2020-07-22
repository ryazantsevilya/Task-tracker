<?php
namespace App\Controllers;
use App\Models\Task;
use Rakit\Validation\Validator;

class TaskController extends Controller
{
    private $taskStore;
    private $usersStore;

    function __construct() {
        $dataDir = $_ENV['DB_PATH'];
        $this->taskStore = \SleekDB\SleekDB::store('task', $dataDir);
    }

    public function homeAction($page = 1)
    {
        $sortKey = !empty($_REQUEST['sort_key']) ? $_REQUEST['sort_key'] : null;
        $sortBy = !empty($_REQUEST['sort_by']) ? $_REQUEST['sort_by'] : 'desc';

        $tasks = $this->taskStore->fetch();
        $total = count($tasks);
        $limit = 3;

        $pages = ceil($total / $limit);
        $offset = ($page - 1)  * $limit;

        $start = $offset + 1;
        $end = min(($offset + $limit), $total);

        if (!empty($sortKey)){
            $tasks = $this->taskStore->orderBy($sortBy, $sortKey);
        } else {
            $tasks = $this->taskStore;
        }
        
        $tasks = $tasks->skip($start - 1)
                        ->limit($limit)
                        ->fetch();

        return $this->render('tasks',
        [
            'tasks' => $tasks, 
            'current_page' => $page,
            'pages' => $pages,
            'count' => $total,
            'start' =>$start,
            'end' => $end,
            'sorted_key' => $sortKey,
            'sorted_by' => $sortBy
        ]);
    }

    public function createPage(){
        return $this->render('taskForm');
    }

    public function createAction(){
        $validator = new Validator;

        $validation = $validator->make($_POST, [
            'username' => 'required|max:255',
            'email' => 'required|email',
            'content' => 'required|max:4086',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();

            return $this->render('taskForm',[
                'errors' => $errors->firstOfAll()
            ]);
        } else {
            $newTask = new Task();
            $newTask->username = $_POST['username'];
            $newTask->content = $_POST['content'];
            $newTask->email = $_POST['email'];
            $newTask->status = 'NEW';
            $newTask->isChanged = false;

            $this->taskStore->insert((array)$newTask);

            return $this->render('taskForm',[
                'alerts' => ['New task create sucsess!']
            ]);
        }
    }

    public function editPage($id){
        $task = $this->taskStore->where('_id', '=', $id)->fetch();

        if (!reset($task)){
            return $this->render('editTask',[
                'errors' => ['Task not found']
            ]);
        }

        return $this->render('editTask',[
            'task' => reset($task)
        ]);
    }

    public function editAction($id){
        if ($_SESSION['auth']){
            $task = $this->taskStore->where('_id', '=', $id)->fetch();
            $task = reset($task);

            $validator = new Validator;
            
            $validation = $validator->make($_POST, [
                'content' => 'required|max:4086',
                'status' => [
                    'required', 
                    $validator('in', ['Ready', 'NEW'])->strict()
                ]
            ]);
    
            if ($validation->fails()) {
                $errors = $validation->errors();
    
                return $this->render('editTask',[
                    'errors' => $errors->firstOfAll(),
                    'task' => $task
                ]);
            } else {
                $isChanged = $task['isChanged'];
                
                if (!$isChanged) {
                    $isChanged = ($_POST['content'] != $task['content']) ? true : false;
                }
    
                $isUpdate = $this->taskStore->where('_id', '=', $id)->update([
                    'content' => $_POST['content'],
                    'status' => (!empty($_POST['status']) && $_POST['status'] == 'true') ? 'READY' : 'NEW',
                    'isChanged' => $isChanged
                ]);
    
                $task = $this->taskStore->where('_id', '=', $id)->fetch();
                $task = reset($task);
    
                if ($isUpdate){
                    return $this->render('editTask',[
                        'alerts' => ['Task updated success!'],
                        'task' => $task
                    ]);
                } else {
                    return $this->render('editTask',[
                        'errors' => ['Task updated failed!'],
                        'task' => $task
                    ]);
                }
            }
            if (!reset($task)){
                return $this->render('editTask',[
                    'errors' => ['Task not found']
                ]);
            }
        } else {
            header('Location: /login');
            exit();
        }
    }
}