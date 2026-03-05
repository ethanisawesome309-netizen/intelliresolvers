//usr/bin/env jbang "$0" "$@" ; exit $?
//DEPS org.apache.camel:camel-bom:4.0.0@pom
//DEPS org.apache.camel:camel-core
//DEPS org.apache.camel:camel-main
//DEPS org.apache.camel:camel-sql
//DEPS org.apache.camel:camel-jackson
//DEPS org.apache.camel:camel-file
//DEPS mysql:mysql-connector-java:8.0.33

import org.apache.camel.builder.RouteBuilder;
import org.apache.camel.main.Main;
import org.apache.camel.model.dataformat.JsonLibrary;
import static org.apache.camel.builder.Builder.simple;

public class TicketRouter extends RouteBuilder {

    public static void main(String[] args) throws Exception {
        Main main = new Main();
        main.configure().addRoutesBuilder(new TicketRouter());
        main.run(args);
    }

    @Override
    public void configure() throws Exception {
        
        // --- RESUME FEATURE: ERROR HANDLING ---
        // If DB fails, retry 3 times with a 2-second delay
        onException(Exception.class)
            .maximumRedeliveries(3)
            .redeliveryDelay(2000)
            .handled(true)
            .log("❌ Permanent Failure processing ticket: ${header.id}");

        // --- THE ROUTE ---
        from("sql:SELECT * FROM tickets WHERE status_id = 1 AND processed = 0?delay=5000")
            .routeId("TicketDiscovery")
            .marshal().json(JsonLibrary.Jackson)
            
            
            .choice()
                .when(simple("${body} contains 'Urgent'"))
                    .log("🔥 URGENT TICKET DETECTED: ${header.id}")
                    .to("seda:urgent")
                .otherwise()
                    .log("Standard Ticket: ${header.id}")
            .end()
            
            
            .wireTap("file:/home/site/wwwroot/?fileName=node_logs.txt&fileExist=Append")
            
            .setBody(simple("UPDATE tickets SET processed = 1 WHERE id = :#id"))
            .to("jdbc:dataSource");
    }
}