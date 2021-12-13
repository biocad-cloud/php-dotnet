<?php

include_once __DIR__ . "/commandLine.php";

/**
 * A self-sufficient runtime for containers
*/
class docker {

    #region "cli"

    /**
        Usage:  docker [OPTIONS] COMMAND

    A self-sufficient runtime for containers

    Options:
        --config string      Location of client config files (default "/root/.docker")
    -c, --context string     Name of the context to use to connect to the daemon (overrides DOCKER_HOST env var and default context set with "docker context use")
    -D, --debug              Enable debug mode
    -H, --host list          Daemon socket(s) to connect to
    -l, --log-level string   Set the logging level ("debug"|"info"|"warn"|"error"|"fatal") (default "info")
        --tls                Use TLS; implied by --tlsverify
        --tlscacert string   Trust certs signed only by this CA (default "/root/.docker/ca.pem")
        --tlscert string     Path to TLS certificate file (default "/root/.docker/cert.pem")
        --tlskey string      Path to TLS key file (default "/root/.docker/key.pem")
        --tlsverify          Use TLS and verify the remote
    -v, --version            Print version information and quit

    Management Commands:
    builder     Manage builds
    config      Manage Docker configs
    container   Manage containers
    context     Manage contexts
    engine      Manage the docker engine
    image       Manage images
    network     Manage networks
    node        Manage Swarm nodes
    plugin      Manage plugins
    secret      Manage Docker secrets
    service     Manage services
    stack       Manage Docker stacks
    swarm       Manage Swarm
    system      Manage Docker
    trust       Manage trust on Docker images
    volume      Manage volumes

    Commands:
    attach      Attach local standard input, output, and error streams to a running container
    build       Build an image from a Dockerfile
    commit      Create a new image from a container's changes
    cp          Copy files/folders between a container and the local filesystem
    create      Create a new container
    diff        Inspect changes to files or directories on a container's filesystem
    events      Get real time events from the server
    exec        Run a command in a running container
    export      Export a container's filesystem as a tar archive
    history     Show the history of an image
    images      List images
    import      Import the contents from a tarball to create a filesystem image
    info        Display system-wide information
    inspect     Return low-level information on Docker objects
    kill        Kill one or more running containers
    load        Load an image from a tar archive or STDIN
    login       Log in to a Docker registry
    logout      Log out from a Docker registry
    logs        Fetch the logs of a container
    pause       Pause all processes within one or more containers
    port        List port mappings or a specific mapping for the container
    ps          List containers
    pull        Pull an image or a repository from a registry
    push        Push an image or a repository to a registry
    rename      Rename a container
    restart     Restart one or more containers
    rm          Remove one or more containers
    rmi         Remove one or more images
    run         Run a command in a new container
    save        Save one or more images to a tar archive (streamed to STDOUT by default)
    search      Search the Docker Hub for images
    start       Start one or more stopped containers
    stats       Display a live stream of container(s) resource usage statistics
    stop        Stop one or more running containers
    tag         Create a tag TARGET_IMAGE that refers to SOURCE_IMAGE
    top         Display the running processes of a container
    unpause     Unpause all processes within one or more containers
    update      Update configuration of one or more containers
    version     Show the Docker version information
    wait        Block until one or more containers stop, then print their exit codes

    Run 'docker COMMAND --help' for more information on a command.

    */
    
    #endregion

    /**
     * docker run
     * 
     * Run a command in a new container
     * 
     * The docker run command first creates a writeable container
     * layer over the specified image, and then starts it using the specified
     * command. That is, docker run is equivalent to the API
     * \code{/containers/create} then \code{/containers/(id)/start}. A stopped
     * container can be restarted with all its previous changes intact using
     * docker start. See docker ps -a to view a list of all containers.
     *
     * The docker run command can be used in combination with docker commit to
     * change the command that a container runs. There is additional detailed
     * information about docker run in the Docker run reference.
     *
     * For information on connecting a container to a network, see the
     * \code{Docker network overview}.
     * 
     * @param string $workdir Working directory inside the container
     * @param string $name Assign a name to the container
     * @param array $volume Bind mount a volume, see \link{volumeBind}.
    */
    public static function run($container, $commandline, $workdir = "./", $volume = NULL, $tty = FALSE, $volumes_from = NULL, $name = NULL) {
        if (empty($volume)) {
            $volume = [];
        }
    
        $volume["docker.sock"] = ["host" => "/var/run/docker.sock", "virtual" => "/var/run/docker.sock"];
        $volume["docker"]      = ["host" => "$(which docker)", "virtual" => "/bin/docker"];
    
        $args = [
            "--workdir" => realpath($workdir),
            "--name"    => $name,
            "--volume"  => \docker\CommandLine::volumeBind($volume),
            "--volumes-from"  => $volumes_from
        ];
        $tty = $tty ? "-t" : "";

        $cli = "%s %s --privileged=true %s %s";
        $cli = sprintf($cli, \docker\CommandLine::commandlineArgs("run", $args), $tty, $container, $commandline);
        
        if (IS_CLI && APP_DEBUG) {
            console::log("commandline string for run docker container:");
            console::log($cli);
        }

        $stdout = shell_exec($cli);

        return $stdout;
    }

    /**
     * Run command line in a running docker container
     * 
     * @param string $name the name of the virtual machine or the container id
     * @param string $commandline the commandline the will be running in target docker container.
     * 
     * @return string the ``std_output`` of the commandline that running in target
     *      container.
    */
    public static function exec($name, $commandline) {
        $cli = "docker exec -t $name $commandline";
        $stdout = shell_exec($cli);

        return $stdout;
    }
}