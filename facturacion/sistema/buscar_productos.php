<?php 
    session_start();
    include "..//conexion.php";
    //print_r($_REQUEST); exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php"?>
	<title>Lista de Productos</title>
</head>
<body>
	<?php include "includes/header.php"; ?>
	<section id="container">

        <?php 
            $busqueda = '';
            $search_proveedor = '';

            if(empty($_REQUEST['busqueda']) && empty($_REQUEST['proveedor'])){
                header('location: lista_productos.php');
            }
            if(!empty($_REQUEST['busqueda'])){
                $busqueda = strtolower($_REQUEST['busqueda']);
                $where = "(p.codproducto LIKE '%$busqueda%' OR p.descripcion LIKE '%$busqueda%') AND p.estatus = 1";
                $buscar = 'busqueda='.$busqueda;
   
            }
            if(!empty($_REQUEST['proveedor'])){
                $search_proveedor = ($_REQUEST['proveedor']);
                $where = "p.proveedor LIKE $search_proveedor AND p.estatus = 1";
                $buscar = 'proveedor='.$search_proveedor;
            }
        ?>
        <i class="fas fa-cube fa-2x"></i>
        <h1>Lista de productos</h1>
        <a href="registro_producto.php" class="btn_new"><i class="fas fa-plus"></i> Registrar Producto</a>
        
        <form action="buscar_productos.php"  method="get" class="form_search">
            <input type="text" name="busqueda" id="busqueda" placeholder="Buscar" value="<?php echo $busqueda;?>">
            <button type="submit" class="btn_search"><i class="fas fa-search"></i></button>

        </form>
        <table>
            <tr>
                <th>CÓDIGO</th>
                <th>DESCRIPCIÓN</th>
                <th>PRECIO</th>
                <th>EXISTENCIA</th>
                <th>
                <?php
                
                    $pro = 0;
                    if(!empty($_REQUEST['proveedor'])){
                        $pro = $_REQUEST['proveedor'];
                        }
                    $query_proveedor= mysqli_query($conection,"SELECT * FROM proveedor WHERE estatus = 1 ORDER BY proveedor ASC"); 
                                                    

                    $result_proveedor= mysqli_num_rows($query_proveedor);
                    
                ?>
                <select name="proveedor" id="search_proveedor">
                    <option value="" selected>PROVEEDOR</option>
                <?php 
                    if($result_proveedor > 0) 
                    {
                        while($proveedor = mysqli_fetch_array($query_proveedor)) {
                            if($pro == $proveedor['codproveedor'])
                            {
            
                ?>
                    <option value="<?php echo $proveedor['codproveedor']; ?>" selected><?php echo $proveedor['proveedor']; ?></option>
                <?php
                    }else{
                ?>
                    <option value="<?php echo $proveedor['codproveedor']; ?>"><?php echo $proveedor['proveedor']; ?></option>
                <?php        
                    } 
                    }
                }
                ?>
                
            </select>
                </th>
                <th>FOTO</th>
                <th>ACCIONES</th>
            </tr>
            
            <?php 
                //Paginador//

                $sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM producto as p  
                                                                        WHERE $where");
                                                       
                $reuslt_register = mysqli_fetch_array($sql_registe);
                $total_registro = $reuslt_register['total_registro'];

                $por_pagina = 3;

                if(empty($_GET['pagina']))
                {
                    $pagina = 1;
                }else{
                    $pagina = $_GET['pagina'];
                }

                $desde = ($pagina-1) * $por_pagina;
                $total_paginas = ceil($total_registro / $por_pagina);

                $query = mysqli_query($conection,"SELECT p.codproducto, p.descripcion, .p.precio, p.existencia, pr.proveedor, p.foto 
                                                    FROM producto p 
                                                    INNER JOIN proveedor pr
                                                    ON p.proveedor = pr.codproveedor
                                                    WHERE $where
                                                    ORDER BY p.codproducto ASC LIMIT $desde,$por_pagina");

                mysqli_close($conection);
                $reuslt = mysqli_num_rows($query);

                if($reuslt > 0)
                {
                    while ($data = mysqli_fetch_array($query)) {
                        if($data['foto'] != 'img_producto.png'){
                            $foto= 'img/'.$data['foto'];
                        }else{
                            $foto= 'img/'.$data['foto'];
                        }
                ?>

                    <tr class="row<?php echo$data["codproducto"];?>"> 
                        <td><?php echo $data['codproducto'];?></td>
                        <td><?php echo $data['descripcion'];?></td>
                        <td class="celPrecio"><?php echo $data['precio'];?></td>
                        <td class="celExistencia"><?php echo $data['existencia'];?></td>
                        <td><?php echo $data['proveedor'];?></td>
                        <td class="img_producto"><img src="<?php echo $foto;?>" alt="<?php echo $data['descripcion'];?>"></td>
                        <?php if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) { ?>
                        <td>
                            <a class="link_add add_product" product="<?php echo $data['codproducto']; ?>" href="#"><i class="fas fa-plus"></i> Agregar</a>
                            |
                            <a class="link_edit" href="editar_producto.php?id=<?php echo $data['codproducto']; ?>"><i class="far fa-edit"></i> Editar</a>
                            
                            |
                            <a class="link_delete del_product" href="#" product="<?php echo $data['codproducto']; ?>"><i class="far fa-trash-alt"></i> Eliminar</a>
                        </td>
                        <?php } ?>
                    </tr>
                <?php
                }
            }
                 ?>               
        </table>
        <?php 
            if($total_paginas != 0)
            {

        ?>
        <div class="paginador">
                <ul>
                <?php 
                    if($pagina != 1) 
                    {
                ?>      
                    <li><a href="?pagina=<?php echo 1; ?>&<?php echo $buscar; ?>"><i class="fas fa-caret-left fa-lg"></i></a></li>
                    <li><a href="?pagina=<?php echo $pagina-1; ?>&<?php echo $buscar; ?>"><i class="fas fa-backward"></i></a></li>
                <?php 
                    }
                    for($i=1; $i <= $total_paginas; $i++) {
                        if($i == $pagina)
                        {
                            echo '<li class="pageSelected">'.$i.'</li>';
                        }else{
                            echo '<li><a href="?pagina='.$i.'&'.$buscar.'">'.$i.'</a></li>';
                        }
                    }
                    if($pagina != $total_paginas)
                    {   
                ?>  
                    <li><a href="?pagina=<?php echo $pagina +1; ?>&<?php echo $buscar; ?>"><i class="fas fa-caret-right fa-lg"></i></a></li>
                    <li><a href="?pagina=<?php echo $total_paginas; ?>&<?php echo $buscar; ?>"><i class="fas fa-forward"></i></a></li>
                     
                <?php } ?>
                </ul>
        </div>
        <?php } ?>
	</section>

	<?php include "includes/footer.php"; ?>
</body>
</html>